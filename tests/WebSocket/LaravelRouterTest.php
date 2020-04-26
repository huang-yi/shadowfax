<?php

namespace HuangYi\Shadowfax\Tests\WebSocket;

use HuangYi\Shadowfax\WebSocket\LaravelRouter;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Routing\Route;
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
}
