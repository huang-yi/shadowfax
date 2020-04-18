<?php

namespace HuangYi\Shadowfax\Tests\Factories;

use HuangYi\Shadowfax\EventDispatcher;
use HuangYi\Shadowfax\Factories\WebSocketServerFactory;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;
use Swoole\WebSocket\Server;

class WebSocketServerFactoryTest extends TestCase
{
    public function testCreate()
    {
        $factory = new WebSocketServerFactory($this->mockDispatcher());

        $server = $factory->create();

        $this->assertInstanceOf(Server::class, $server);

        unset($server);
    }

    protected function mockDispatcher()
    {
        $dispatcher = m::mock(EventDispatcher::class);

        $dispatcher->shouldReceive('dispatch')->times()->andReturn(new stdClass);

        return $dispatcher;
    }
}
