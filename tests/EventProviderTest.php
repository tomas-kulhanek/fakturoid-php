<?php

namespace Fakturoid\Tests;

use Fakturoid\Dispatcher;
use Fakturoid\Provider\EventProvider;
use Fakturoid\Response;

class EventProviderTest extends TestCase
{
    public function testList(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $responseInterface = $this->createPsrResponseMock(200, 'application/json', '{}');

        $dispatcher->expects($this->once())
            ->method('get')
            ->with('/events.json', [])
            ->willReturn(new Response($responseInterface));

        $provider = new EventProvider($dispatcher);
        $response = $provider->list();
        $this->assertEquals([], $response->getBody(true));
    }

    public function testlistPaid(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);

        $responseInterface = $this->createPsrResponseMock(200, 'application/json', '{"page": 2}');
        $dispatcher->expects($this->once())
            ->method('get')
            ->with('/events/paid.json', ['page' => 2])
            ->willReturn(new Response($responseInterface));

        $provider = new EventProvider($dispatcher);
        $response = $provider->listPaid(['page' => 2]);
        $this->assertEquals(['page' => 2], $response->getBody(true));
    }
}
