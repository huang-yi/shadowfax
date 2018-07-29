<?php

namespace HuangYi\Swoole\Tests\WebSocket;

use HuangYi\Swoole\Exceptions\EventNotFoundException;
use HuangYi\Swoole\Exceptions\WebSocketException;
use HuangYi\Swoole\WebSocket\Event;
use HuangYi\Swoole\WebSocket\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    /**
     * @var \HuangYi\Swoole\WebSocket\Route
     */
    protected $route;

    public function setUp()
    {
        parent::setUp();

        $this->route = new Route('GET', '/', function () {});
    }

    public function testOn()
    {
        $this->route->on('test', function() {});

        $this->assertCount(1, $this->route->getEvents());
        $this->assertInstanceOf(Event::class, $this->route->getEvents()['test']);
    }

    public function testInvalidCallback()
    {
        $this->expectException(WebSocketException::class);

        $this->route->on('test', 'UndefinedClass@method');
    }

    public function testGetEvent()
    {
        $this->route->on('test', function() {});

        $this->assertInstanceOf(Event::class, $this->route->getEvent('test'));
    }

    public function testEventNotFound()
    {
        $this->expectException(EventNotFoundException::class);

        $this->route->getEvent('undefined');
    }
}
