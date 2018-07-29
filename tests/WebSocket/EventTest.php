<?php

namespace HuangYi\Swoole\Tests\WebSocket;

use HuangYi\Swoole\WebSocket\Event;
use HuangYi\Swoole\WebSocket\Message;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testGetName()
    {
        $event = new Event('test', function () {});

        $this->assertEquals('test', $event->getName());
    }

    public function testGetCallback()
    {
        $callback = function ($message) {};

        $event = new Event('test', $callback);

        $this->assertEquals($callback, $event->getCallback());
    }

    public function testFire()
    {
        $message = new Message('test', ['hello world']);

        $event = new Event('test', function ($eventMessage) use ($message) {
            $this->assertEquals($eventMessage->getEvent(), $message->getEvent());
            $this->assertEquals($eventMessage->getData(), $message->getData());
        });

        $event->fire($message);
    }
}
