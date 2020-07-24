<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use HuangYi\Shadowfax\Laravel\AppPool;
use HuangYi\Shadowfax\Laravel\FrameworkBootstrapper;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
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


    public function testFacadeInstancesCleared()
    {
        $pool = new AppPool($this->mockFrameworkBootstrapper());

        ShadowfaxFacade::setResolvedInstances(['foo' => 'bar']);

        $pool->pop();

        $this->assertEmpty(ShadowfaxFacade::getResolvedInstances());
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


class ShadowfaxFacade extends Facade
{
    public static function setResolvedInstances(array $instances)
    {
        static::$resolvedInstance = $instances;
    }

    public static function getResolvedInstances()
    {
        return static::$resolvedInstance;
    }
}
