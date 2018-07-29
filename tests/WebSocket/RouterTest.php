<?php

namespace HuangYi\Swoole\Tests\WebSocket;

use HuangYi\Swoole\WebSocket\Route;
use HuangYi\Swoole\WebSocket\Router;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testRoom()
    {
        $router = new Router(new Dispatcher());

        $route = $router->room('/', function () {});

        $this->assertInstanceOf(Route::class, $route);
    }
}
