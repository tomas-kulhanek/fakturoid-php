<?php

namespace Fakturoid\Tests;

use DateTimeImmutable;
use Fakturoid\Auth\AuthProvider;
use Fakturoid\Auth\Credentials;
use Fakturoid\Enum\AuthTypeEnum;
use Fakturoid\Exception\AuthorizationFailedException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class AuthProviderTest extends TestCase
{
    public function testAuthenticationUrl(): void
    {
        $requester = $this->createMock(ClientInterface::class);
        $authProvider = new AuthProvider('clientId', 'clientSecret', 'redirectUri', $requester);

        $this->assertEquals(
            'https://app.fakturoid.cz/api/v3/oauth?client_id=clientId&redirect_uri=redirectUri&response_type=code&state=c',
            $authProvider->getAuthenticationUrl('c')
        );
    }

    public function testAuthenticationUrlWithoutState(): void
    {
        $requester = $this->createMock(ClientInterface::class);
        $authProvider = new AuthProvider('clientId', 'clientSecret', 'redirectUri', $requester);

        $this->assertEquals(
            'https://app.fakturoid.cz/api/v3/oauth?client_id=clientId&redirect_uri=redirectUri&response_type=code',
            $authProvider->getAuthenticationUrl()
        );
    }

    public function testAuthorizationCodeReAuthWithEmptyRefreshCode(): void
    {
        $requester = $this->createMock(ClientInterface::class);

        $credentials = $this->createMock(Credentials::class);
        $credentials->method('getAuthType')
            ->willReturn(AuthTypeEnum::AUTHORIZATION_CODE_FLOW);
        $credentials->method('getAccessToken')
            ->willReturn('access_token');
        $credentials->method('getRefreshToken')
            ->willReturn(null);

        $authProvider = new AuthProvider('clientId', 'clientSecret', null, $requester);
        $authProvider->setCredentials($credentials);
        $this->expectException(AuthorizationFailedException::class);
        $this->expectExceptionMessage('Invalid credentials');
        $authProvider->reAuth();
    }

    public function testEmptyCredentialsReAuth(): void
    {
        $requester = $this->createMock(ClientInterface::class);

        $authProvider = new AuthProvider('clientId', 'clientSecret', null, $requester);
        $this->expectException(AuthorizationFailedException::class);
        $this->expectExceptionMessage('Invalid credentials');
        $authProvider->reAuth();
    }

    public function testAuthorizationCodeReAuth(): void
    {
        $responseInterface = $this->createMock(ResponseInterface::class);
        $responseInterface
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getStreamMock('{"refresh_token":"","access_token":"access_token","expires_in":7200}'));

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $credentials = $this->createMock(Credentials::class);
        $credentials->method('getAuthType')
            ->willReturn(AuthTypeEnum::AUTHORIZATION_CODE_FLOW);
        $credentials
            ->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('access_token');
        $credentials
            ->expects($this->exactly(2))
            ->method('getRefreshToken')
            ->willReturn('refresh_token');
        $credentials
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);


        $authProvider = new AuthProvider('clientId', 'clientSecret', null, $client);
        $authProvider->setCredentials($credentials);
        $authProvider->reAuth();
    }

    public function testClientCredentialReAuth(): void
    {
        $responseInterface = $this->createMock(ResponseInterface::class);
        $responseInterface
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getStreamMock('{"refresh_token":"","access_token":"access_token","expires_in":7200}'));

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $credentials = $this->createMock(Credentials::class);
        $credentials->method('getAuthType')
            ->willReturn(AuthTypeEnum::AUTHORIZATION_CODE_FLOW);
        $credentials
            ->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('access_token');
        $credentials
            ->expects($this->exactly(2))
            ->method('getRefreshToken')
            ->willReturn('refresh_token');
        $credentials
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);

        $authProvider = new AuthProvider('clientId', 'clientSecret', null, $client);
        $authProvider->setCredentials($credentials);
        $authProvider->reAuth();
    }

    public function testClientCredentialReAuthWithError(): void
    {
        $responseInterface = $this->createMock(ResponseInterface::class);
        $responseInterface
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getStreamMock('{"error":"invalid_grant"}'));

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $credentials = $this->createMock(Credentials::class);
        $credentials->method('getAuthType')
            ->willReturn(AuthTypeEnum::AUTHORIZATION_CODE_FLOW);
        $credentials
            ->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('access_token');
        $credentials
            ->expects($this->exactly(2))
            ->method('getRefreshToken')
            ->willReturn('refresh_token');
        $credentials
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);

        $authProvider = new AuthProvider('clientId', 'clientSecret', null, $client);
        $this->expectException(AuthorizationFailedException::class);
        $this->expectExceptionMessage('Error occurred while refreshing token. Message: invalid_grant');
        $authProvider->setCredentials($credentials);
        $authProvider->reAuth();
    }


    public function testClientCredentialReAuthWithoutResponse(): void
    {
        $responseInterface = $this->createMock(ResponseInterface::class);
        $responseInterface
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getStreamMock('{}'));

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $credentials = $this->createMock(Credentials::class);
        $credentials->method('getAuthType')
            ->willReturn(AuthTypeEnum::AUTHORIZATION_CODE_FLOW);
        $credentials
            ->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('access_token');
        $credentials
            ->expects($this->exactly(2))
            ->method('getRefreshToken')
            ->willReturn('refresh_token');
        $credentials
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);

        $authProvider = new AuthProvider('clientId', 'clientSecret', null, $client);
        $this->expectException(AuthorizationFailedException::class);
        $this->expectExceptionMessage('Error occurred while refreshing token. Message: Invalid response');
        $authProvider->setCredentials($credentials);
        $authProvider->reAuth();
    }

    public function testClientCredential(): void
    {
        $responseInterface = $this->createMock(ResponseInterface::class);
        $responseInterface
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getStreamMock('{"refresh_token":"","access_token":"access_token","expires_in":7200}'));

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);
        $authProvider = new AuthProvider('clientId', 'clientSecret', null, $client);

        $credentials = $authProvider->auth(AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW);
        $this->assertInstanceOf(Credentials::class, $credentials);
        $this->assertEquals('access_token', $credentials->getAccessToken());
        $this->assertEquals(null, $credentials->getRefreshToken());
        $this->assertEquals(AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW, $credentials->getAuthType());
    }

    public function testClientCredentialWithEmptyResponse(): void
    {
        $responseInterface = $this->createMock(ResponseInterface::class);
        $responseInterface
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getStreamMock('{}'));

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);
        $authProvider = new AuthProvider('clientId', 'clientSecret', null, $client);

        $this->expectException(AuthorizationFailedException::class);
        $this->expectExceptionMessage('Error occurred while refreshing token. Message: Invalid response');
        $authProvider->auth(AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW);
    }


    public function testAuthorizationCodeWithoutCode(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $authProvider = new AuthProvider('clientId', 'clientSecret', null, $client);

        $this->expectException(AuthorizationFailedException::class);
        $this->expectExceptionMessage('Load authentication screen first.');
        $authProvider->auth(AuthTypeEnum::AUTHORIZATION_CODE_FLOW);
    }


    public function testAuthorizationCodeSimple(): void
    {
        $responseInterface = $this->createMock(ResponseInterface::class);
        $responseInterface
            ->expects($this->once())
            ->method('getBody')
            ->willReturn(
                $this->getStreamMock(
                    '{"refresh_token":"refresh_token","access_token":"access_token","expires_in":7200}'
                )
            );

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);
        $authProvider = new AuthProvider('clientId', 'clientSecret', 'redirectUri', $client);
        $authProvider->requestCredentials('CODE');
        $credentials = $authProvider->getCredentials();
        $this->assertInstanceOf(Credentials::class, $credentials);
        $this->assertEquals('access_token', $credentials->getAccessToken());
        $this->assertEquals('refresh_token', $credentials->getRefreshToken());
        $this->assertEquals(AuthTypeEnum::AUTHORIZATION_CODE_FLOW, $credentials->getAuthType());
    }

    public function testAuthorizationCode(): void
    {
        $responseInterface = $this->createMock(ResponseInterface::class);
        $responseInterface
            ->expects($this->once())
            ->method('getBody')
            ->willReturn(
                $this->getStreamMock(
                    '{"refresh_token":"refresh_token","access_token":"access_token","expires_in":7200}'
                )
            );

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);
        $authProvider = new AuthProvider('clientId', 'clientSecret', 'redirectUri', $client);
        $authProvider->loadCode('CODE');
        $credentials = $authProvider->auth(AuthTypeEnum::AUTHORIZATION_CODE_FLOW);
        $this->assertInstanceOf(Credentials::class, $credentials);
        $this->assertEquals('access_token', $credentials->getAccessToken());
        $this->assertEquals('refresh_token', $credentials->getRefreshToken());
        $this->assertEquals(AuthTypeEnum::AUTHORIZATION_CODE_FLOW, $credentials->getAuthType());
    }

    public function testJsonCredentials(): void
    {
        $lastValidation = new DateTimeImmutable();
        $credentials = new Credentials(
            'refresh_token',
            'access_token',
            7200,
            $lastValidation,
            AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW
        );

        $this->assertEquals(
            json_encode([
                'refresh_token' => 'refresh_token',
                'access_token' => 'access_token',
                'expires_in' => 7200,
                'lastValidation' => $lastValidation->format(DateTimeImmutable::ATOM),
                'authType' => AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW->value,
            ]),
            $credentials->toJson()
        );
    }

    public function testExpiration(): void
    {
        $lastValidation = new DateTimeImmutable();
        $credentials = new Credentials(
            'refresh_token',
            'access_token',
            7200,
            $lastValidation,
            AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW
        );
        $this->assertFalse($credentials->isExpired());

        $lastValidation = (new DateTimeImmutable())->modify('-10 seconds');
        $credentials = new Credentials(
            'refresh_token',
            'access_token',
            5,
            $lastValidation,
            AuthTypeEnum::CLIENT_CREDENTIALS_CODE_FLOW
        );

        $this->assertTrue($credentials->isExpired());
    }
}
