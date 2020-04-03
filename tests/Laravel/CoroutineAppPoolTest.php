<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use HuangYi\Shadowfax\Laravel\CoroutineAppPool;
use HuangYi\Shadowfax\Laravel\FrameworkBootstrapper;
use Illuminate\Container\Container;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CoroutineAppPoolTest extends TestCase
{
    public function testPoolCapacity()
    {
        $pool = new CoroutineAppPool($this->mockFrameworkBootstrapper(), [], 5);

        $this->assertEquals(5, $pool->getChannel()->length());
    }


    public function testPop()
    {
        $pool = new CoroutineAppPool($this->mockFrameworkBootstrapper(), [], 5);

        $app = $pool->pop();

        $this->assertInstanceOf(Container::class, $app);
        $this->assertEquals(4, $pool->getChannel()->length());
    }


    public function testPush()
    {
        $pool = new CoroutineAppPool($this->mockFrameworkBootstrapper(), [], 5);

        $app = $pool->getChannel()->pop();

        $pool->push($app);

        $this->assertEquals(5, $pool->getChannel()->length());
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
