<?php

namespace HuangYi\Shadowfax\Tests;

use HuangYi\Shadowfax\EventDispatcher;
use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    public function testListen()
    {
        $dispatcher = new EventDispatcher();

        $listener = new Listener();

        $dispatcher->listen('foo', $listener);

        $this->assertTrue($dispatcher->hasEvent('foo'));
        $this->assertSame($listener, $dispatcher->getListen()['foo'][0][0]);
    }


    public function testDispatch()
    {
        $dispatcher = new EventDispatcher();

        $flag = new Flag();
        $event = new Event($flag);
        $listener = new Listener();

        $dispatcher->listen(Event::class, $listener);

        $dispatcher->dispatch($event);

        $this->assertEquals(1, $flag->count);
    }


    public function testListenersPriority()
    {
        $dispatcher = new EventDispatcher();

        $listener1 = new Listener();
        $listener2 = new Listener2();

        $dispatcher->listen('foo', $listener1, 1);
        $dispatcher->listen('foo', $listener2, 2);

        $listeners = $dispatcher->getListeners('foo');

        $this->assertSame([$listener2, $listener1], $listeners);
    }
}

class Flag
{
    public $count = 0;
}

class Event
{
    public $flag;

    public function __construct(Flag $flag)
    {
        $this->flag = $flag;
    }
}

class Listener
{
    public function handle(Event $event)
    {
        $event->flag->count++;
    }
}

class Listener2
{
    public function handle(Event $event)
    {
        //
    }
}
