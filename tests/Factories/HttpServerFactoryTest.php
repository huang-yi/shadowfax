<?php

namespace HuangYi\Shadowfax\Tests\Factories;

use HuangYi\Shadowfax\EventDispatcher;
use HuangYi\Shadowfax\Factories\HttpServerFactory;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;
use Swoole\Http\Server;

class HttpServerFactoryTest extends TestCase
{
    public function testCreate()
    {
        $factory = new HttpServerFactory($this->mockDispatcher());

        $server = $factory->create();

        $this->assertInstanceOf(Server::class, $server);
    }

    protected function mockDispatcher()
    {
        $dispatcher = m::mock(EventDispatcher::class);

        $dispatcher->shouldReceive('dispatch')->times()->andReturn(new stdClass);

        return $dispatcher;
    }
}
