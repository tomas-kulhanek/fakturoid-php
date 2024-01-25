<?php

namespace Fakturoid;

use Fakturoid\Auth\AuthProvider;
use Fakturoid\Exception\AuthorizationFailedException;
use Fakturoid\Exception\BadResponseException;
use Fakturoid\Exception\ClientErrorException;
use Fakturoid\Exception\ConnectionFailedException;
use Fakturoid\Exception\Exception;
use Fakturoid\Exception\InvalidDataException;
use Fakturoid\Exception\ServerErrorException;
use JsonException;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

class Dispatcher implements DispatcherInterface
{
    final public const URL = 'https://app.fakturoid.cz/api/v3/accounts/';

    public function __construct(
        private string $companySlug,
        private readonly string $userAgent,
        private readonly AuthProvider $authorization,
        private readonly ClientInterface $client
    ) {
    }

    public function setCompanySlug(string $companySlug): void
    {
        $this->companySlug = $companySlug;
    }

    /**
     * @param array<string, string> $queryParams
     */
    public function get(string $path, array $queryParams = []): Response
    {
        return $this->dispatch($path, ['method' => 'GET', 'params' => $queryParams]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function post(string $path, array $data = []): Response
    {
        return $this->dispatch($path, ['method' => 'POST', 'data' => $data]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function patch(string $path, array $data): Response
    {
        return $this->dispatch($path, ['method' => 'PATCH', 'data' => $data]);
    }

    public function delete(string $path): Response
    {
        return $this->dispatch($path, ['method' => 'DELETE']);
    }

    /**
     * @param array{'method': string, 'params'?: array<string, mixed>, 'data'?: array<string, mixed>} $options
     * @throws ConnectionFailedException|InvalidDataException|AuthorizationFailedException|BadResponseException
     */
    private function dispatch(string $path, array $options): Response
    {
        $this->authorization->reAuth();
        if ($this->authorization->getCredentials() === null) {
            throw new AuthorizationFailedException('Credentials are null');
        }
        $body = null;
        if (!empty($options['data'])) {
            try {
                $body = json_encode($options['data'], JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new InvalidDataException('Failed to encode data to JSON', $exception->getCode(), $exception);
            }
        }

        try {
            $request = new Request(
                $options['method'],
                self::URL . $this->companySlug . $path,
                [
                    'User-Agent' => $this->userAgent,
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->authorization->getCredentials()->getAccessToken()
                ],
                $body
            );
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ConnectionFailedException($e->getMessage(), $e->getCode(), $e);
        }
        $responseData = new Response($response);

        if ($responseData->getStatusCode() >= 400 && $responseData->getStatusCode() < 500) {
            throw ClientErrorException::createException($responseData);
        }
        if ($responseData->getStatusCode() >= 500 && $responseData->getStatusCode() < 600) {
            throw ServerErrorException::createException($responseData);
        }
        return $responseData;
    }
}