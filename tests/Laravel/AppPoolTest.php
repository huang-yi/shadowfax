<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use HuangYi\Shadowfax\Laravel\AppPool;
use HuangYi\Shadowfax\Laravel\FrameworkBootstrapper;
use Illuminate\Container\Container;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class AppPoolTest extends TestCase
{
    public function testPop()
    {
        $pool = new AppPool($this->mockFrameworkBootstrapper());

        $app = $pool->pop();

        $this->assertInstanceOf(Container::class, $app);
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
