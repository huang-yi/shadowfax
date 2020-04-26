<?php

namespace HuangYi\Shadowfax\Tests\WebSocket;

use HuangYi\Shadowfax\WebSocket\LumenRouter;
use Illuminate\Container\Container;
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
}
