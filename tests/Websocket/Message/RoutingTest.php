<?php

namespace HuangYi\Swoole\Tests\Websocket\Message;

use HuangYi\Swoole\Websocket\Message\Message;
use HuangYi\Swoole\Websocket\Message\Route;
use HuangYi\Swoole\Websocket\Message\Router;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class RoutingTest extends TestCase
{
    /**
     * @var \HuangYi\Swoole\Websocket\Message\Router
     */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Router(new Container);
    }

    public function testRunController()
    {
        $route = new Route('test', ['uses' => 'HuangYi\Swoole\Tests\Websocket\Message\Foo@bar']);
        $route->setContainer(new Container);

        $message = Message::make('test');

        $result = $route->run($message);

        $this->assertEquals('hello', $result);
    }

    public function testRunClosure()
    {
        $route = new Route('test', ['uses' => function () {
            return 'hello';
        }]);
        $route->setContainer(new Container);

        $message = Message::make('test');

        $result = $route->run($message);

        $this->assertEquals('hello', $result);
    }

    public function testFindRoute()
    {
        $this->router->on('test', 'Foo@bar');

        $message = Message::make('test');
        $route = $this->router->findRoute($message);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('test', $route->getEvent());
        $this->assertEquals(['uses' => 'Foo@bar'], $route->getAction());
    }
}

class Foo
{
    public function bar()
    {
        return 'hello';
    }
}
