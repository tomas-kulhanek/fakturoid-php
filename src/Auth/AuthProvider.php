<?php

namespace Fakturoid\Auth;

use Fakturoid\Enum\AuthTypeEnum;
use Fakturoid\Exception\AuthorizationFailedException;
use Fakturoid\Exception\InvalidResponseException;
use JsonException;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

class AuthProvider
{
    private string $authorizeUrl = 'https://app.fakturoid.cz/api/v3/oauth';

    private string $tokenUrl = 'https://app.fakturoid.cz/api/v3/oauth/token';

    private ?string $code = null;

    private ?Credentials $credentials = null;

    private ?CredentialCallback $credentialsCallback = null;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly ?string $redirectUri,
        private readonly ClientInterface $client
    ) {
    }

    public function auth(
        AuthTypeEnum $authType = AuthTypeEnum::AUTHORIZATION_CODE_FLOW,
        Credentials $credentials = null
    ): ?Credentials {
        $this->credentials = $credentials;
        return match ($authType) {
            AuthTypeEnum::AUTHORIZATION_CODE_FLOW => $this->authorizationCode(),
            AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW => $this->clientCredentials()
        };
    }

    /**
     * @throws AuthorizationFailedException
     */
    private function authorizationCode(): ?Credentials
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }
        if (empty($this->code)) {
            throw new AuthorizationFailedException('Load authentication screen first.');
        }

        /** @var array{'access_token': string, 'expires_in': int, 'refresh_token': string, 'token_type': string} $json */
        $json = $this->curl([
            'grant_type' => 'authorization_code',
            'code' => $this->code,
            'redirect_uri' => $this->redirectUri,
        ]);
        $this->credentials = new Credentials(
            $json['refresh_token'],
            $json['access_token'],
            $json['expires_in'],
            new \DateTimeImmutable(),
            AuthTypeEnum::AUTHORIZATION_CODE_FLOW
        );
        $this->credentials->addLastValidation(new \DateTimeImmutable());
        $this->callCredentialsCallback();
        return $this->credentials;
    }

    /**
     * @throws AuthorizationFailedException
     */
    public function oauth2Refresh(): ?Credentials
    {
        if ($this->credentials !== null) {
            $json = $this->curl([
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->credentials->getRefreshToken(),
                'redirect_uri' => $this->redirectUri,
            ]);
            if (!empty($json['error'])) {
                throw new AuthorizationFailedException(
                    'Error occurred while refreshing token. Message: ' . $json['error']
                );
            }
            if (empty($json['access_token']) || empty($json['expires_in'])) {
                throw new AuthorizationFailedException(
                    'Error occurred while refreshing token. Message: Invalid response'
                );
            }
            $this->credentials = new Credentials(
                $json['refresh_token'] ?? null,
                $json['access_token'],
                $json['expires_in'],
                new \DateTimeImmutable(),
                $this->credentials->getAuthType()
            );
            $this->credentials->addLastValidation(new \DateTimeImmutable());
            $this->credentials->setAuthType(AuthTypeEnum::AUTHORIZATION_CODE_FLOW);
            $this->callCredentialsCallback();
            return $this->credentials;
        }
        return $this->credentials;
    }

    /**
     * @throws AuthorizationFailedException
     */
    public function reAuth(): ?Credentials
    {
        $credentials = $this->getCredentials();
        if (
            $credentials === null
            || empty($credentials->getAccessToken())
            || (empty($credentials->getRefreshToken()) && $credentials->getAuthType(
            ) === AuthTypeEnum::AUTHORIZATION_CODE_FLOW)
        ) {
            throw new AuthorizationFailedException('Invalid credentials');
        }
        if (!$credentials->isExpired()) {
            return $this->getCredentials();
        }
        return match ($credentials->getAuthType()) {
            AuthTypeEnum::AUTHORIZATION_CODE_FLOW => $this->oauth2Refresh(),
            AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW => $this->auth(AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW)
        };
    }

    /**
     * @throws AuthorizationFailedException
     */
    private function clientCredentials(): ?Credentials
    {
        $json = $this->curl([
            'grant_type' => 'client_credentials',
        ]);

        if (empty($json['access_token']) || empty($json['expires_in'])) {
            throw new AuthorizationFailedException(
                'Error occurred while refreshing token. Message: Invalid response'
            );
        }
        $this->credentials = new Credentials(
            $json['refresh_token'] ?? null,
            $json['access_token'],
            $json['expires_in'],
            new \DateTimeImmutable(),
            AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW
        );
        $this->credentials->addLastValidation(new \DateTimeImmutable());
        $this->credentials->setAuthType(AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW);
        $this->callCredentialsCallback();

        return $this->credentials;
    }

    /**
     * @param array<string, mixed> $body
     * @return array{'refresh_token'?: string|null, 'access_token': string, 'expires_in': int}|array{'error'?:string}
     * @throws AuthorizationFailedException|InvalidResponseException
     */
    private function curl(array $body): array
    {
        try {
            $request = new Request(
                'POST',
                $this->tokenUrl,
                [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode(sprintf('%s:%s', $this->clientId, $this->clientSecret))
                ],
                json_encode($body, JSON_THROW_ON_ERROR)
            );
            $response = $this->client->sendRequest($request);
            $responseData = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($responseData)) {
                throw new InvalidResponseException('Invalid response');
            }
            return $responseData;
        } catch (ClientExceptionInterface | JsonException $exception) {
            throw new AuthorizationFailedException(
                sprintf('Error occurred while refreshing token. Message: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    public function getAuthenticationUrl(?string $state = null): string
    {
        return sprintf(
            '%s?client_id=%s&redirect_uri=%s&response_type=code',
            $this->authorizeUrl,
            $this->clientId,
            $this->redirectUri
        ) . ($state !== null ? '&state=' . $state : null);
    }

    public function loadCode(string $code): void
    {
        $this->code = $code;
    }

    public function getCredentials(): ?Credentials
    {
        return $this->credentials;
    }

    private function callCredentialsCallback(): void
    {
        if ($this->credentialsCallback !== null) {
            call_user_func($this->credentialsCallback, $this->credentials);
        }
    }

    public function setCredentials(?Credentials $credentials): void
    {
        $this->credentials = $credentials;
    }

    public function setCredentialsCallback(CredentialCallback $callback): void
    {
        $this->credentialsCallback = $callback;
    }

    /**
     * @throws AuthorizationFailedException
     */
    public function requestCredentials(string $code): void
    {
        $this->loadCode($code);
        $this->auth(AuthTypeEnum::AUTHORIZATION_CODE_FLOW);
    }
}
