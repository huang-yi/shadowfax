<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use HuangYi\Shadowfax\Laravel\CoroutineAppPool;
use HuangYi\Shadowfax\Laravel\FrameworkBootstrapper;
use Illuminate\Container\Container;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;
use Swoole\Event;
use Swoole\Coroutine\Scheduler;

class CoroutineAppPoolTest extends TestCase
{
    public function testPoolCapacity()
    {
        Coroutine::create(function () {
            $pool = new CoroutineAppPool($this->mockFrameworkBootstrapper(), [], 5);

            $this->assertEquals(5, $pool->getChannel()->length());
        });

        Event::wait();
    }


    public function testPop()
    {
        Coroutine::create(function () {
            $pool = new CoroutineAppPool($this->mockFrameworkBootstrapper(), [], 5);

            $app = $pool->pop();

            $this->assertInstanceOf(Container::class, $app);
            $this->assertEquals(4, $pool->getChannel()->length());
        });

        Event::wait();
    }


    public function testPush()
    {
        Coroutine::create(function () {
            $pool = new CoroutineAppPool($this->mockFrameworkBootstrapper(), [], 5);

            $app = $pool->getChannel()->pop();

            $pool->push($app);

            $this->assertEquals(5, $pool->getChannel()->length());
        });

        Event::wait();
    }


    protected function mockFrameworkBootstrapper()
    {
        $bootstrapper = m::mock(FrameworkBootstrapper::class);

        $bootstrapper
            ->shouldReceive('bootstrap')
            ->once()
            ->andReturn(new Container);

        return $bootstrapper;
    }
}
