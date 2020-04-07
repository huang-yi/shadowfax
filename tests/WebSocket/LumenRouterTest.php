<?php

namespace HuangYi\Shadowfax\Tests\WebSocket;

use HuangYi\Shadowfax\Http\Request;
use HuangYi\Shadowfax\WebSocket\LumenRouter;
use Illuminate\Container\Container;
use Illuminate\Http\Request as IlluminateRequest;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class LumenRouterTest extends TestCase
{
    public function testListen()
    {
        $router = new LumenRouter(new Container);

        $router->listen('/lumen-listen', $handler = new EmptyHandler);

        $route = $router->getRoutes()['GET/lumen-listen'] ?? null;

        $this->assertNotNull($route);
        $this->assertSame($handler, $route['action']['handler'] ?? null);
    }


    public function testFindHandler()
    {
        $router = new LumenRouter(new Container);

        $router->listen('/lumen-find-handler', $handler = new EmptyHandler);

        $foundHandler = $router->findHandler($this->mockRequest());

        $this->assertSame($handler, $foundHandler);
    }


    protected function mockRequest()
    {
        $request = m::mock(Request::class);

        $request
            ->shouldReceive('getIlluminateRequest')
            ->andReturn(new IlluminateRequest([], [], [], [], [], ['REQUEST_URI' => '/lumen-find-handler']));

        return $request;
    }
}
