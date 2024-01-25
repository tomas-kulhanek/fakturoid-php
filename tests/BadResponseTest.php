<?php

namespace Fakturoid\Tests;

use Fakturoid\Auth\AuthProvider;
use Fakturoid\Auth\Credentials;
use Fakturoid\Dispatcher;
use Fakturoid\Exception\ClientErrorException;
use Fakturoid\Exception\ServerErrorException;
use Psr\Http\Client\ClientInterface;

class BadResponseTest extends TestCase
{
    public function test404(): void
    {
        $responseInterface = $this->createPsrResponseMock(404, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ClientErrorException::class);
        $this->expectExceptionMessage('Record not found');
        $this->expectExceptionCode(404);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }

    public function test400(): void
    {
        $responseInterface = $this->createPsrResponseMock(400, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ClientErrorException::class);
        $this->expectExceptionMessage('Page not found');
        $this->expectExceptionCode(400);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }

    public function test401(): void
    {
        $responseInterface = $this->createPsrResponseMock(401, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ClientErrorException::class);
        $this->expectExceptionMessage('Unauthorized');
        $this->expectExceptionCode(401);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }

    public function test402(): void
    {
        $responseInterface = $this->createPsrResponseMock(402, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ClientErrorException::class);
        $this->expectExceptionMessage('Payment required or account is blocked');
        $this->expectExceptionCode(402);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }

    public function test403(): void
    {
        $responseInterface = $this->createPsrResponseMock(403, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ClientErrorException::class);
        $this->expectExceptionMessage('Forbidden');
        $this->expectExceptionCode(403);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }


    public function test415(): void
    {
        $responseInterface = $this->createPsrResponseMock(415, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ClientErrorException::class);
        $this->expectExceptionMessage('Unsupported media type');
        $this->expectExceptionCode(415);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }


    public function test422(): void
    {
        $responseInterface = $this->createPsrResponseMock(422, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ClientErrorException::class);
        $this->expectExceptionMessage('Unprocessable entity');
        $this->expectExceptionCode(422);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }


    public function test429(): void
    {
        $responseInterface = $this->createPsrResponseMock(429, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ClientErrorException::class);
        $this->expectExceptionMessage('Too many requests');
        $this->expectExceptionCode(429);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }


    public function testOtherClient(): void
    {
        $responseInterface = $this->createPsrResponseMock(499, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ClientErrorException::class);
        $this->expectExceptionMessage('Client error');
        $this->expectExceptionCode(499);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }


    public function testOtherServer(): void
    {
        $responseInterface = $this->createPsrResponseMock(599, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ServerErrorException::class);
        $this->expectExceptionMessage('Server error');
        $this->expectExceptionCode(599);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }


    public function test503(): void
    {
        $responseInterface = $this->createPsrResponseMock(503, 'application/json', '{"error":""}');
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturn($responseInterface);

        $authProvider = $this->createMock(AuthProvider::class);
        $credentials = $this->createMock(Credentials::class);
        $credentials->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test');
        $authProvider->expects($this->exactly(2))
            ->method('getCredentials')
            ->willReturn($credentials);

        $dispatcher = new Dispatcher('companySlug', 'test', $authProvider, $client);
        $this->expectException(ServerErrorException::class);
        $this->expectExceptionMessage('Fakturoid is in read only state');
        $this->expectExceptionCode(503);
        $dispatcher->patch('/invoices/1.json', ['name' => 'Test']);
    }
}
