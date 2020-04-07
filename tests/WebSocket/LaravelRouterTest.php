<?php

namespace HuangYi\Shadowfax\Tests\WebSocket;

use HuangYi\Shadowfax\Http\Request;
use HuangYi\Shadowfax\WebSocket\LaravelRouter;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Routing\Route;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class LaravelRouterTest extends TestCase
{
    public function testListen()
    {
        $router = new LaravelRouter(new Dispatcher());

        $router->listen('/laravel-listen', $handler = new EmptyHandler);

        $route = $router->getRoutes()->match(new IlluminateRequest([], [], [], [], [], ['REQUEST_URI' => '/laravel-listen']));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($handler, $route->getAction('handler'));
    }


    public function testFindHandler()
    {
        $router = new LaravelRouter(new Dispatcher());

        $router->listen('/laravel-find-handler', $handler = new EmptyHandler);

        $foundHandler = $router->findHandler($this->mockRequest());

        $this->assertSame($handler, $foundHandler);
    }


    protected function mockRequest()
    {
        $request = m::mock(Request::class);

        $request
            ->shouldReceive('getIlluminateRequest')
            ->andReturn(new IlluminateRequest([], [], [], [], [], ['REQUEST_URI' => '/laravel-find-handler']));

        return $request;
    }
}
