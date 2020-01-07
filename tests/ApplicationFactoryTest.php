<?php

namespace HuangYi\Shadowfax\Tests;

use HuangYi\Shadowfax\ApplicationFactory;
use HuangYi\Shadowfax\FrameworkBootstrapper;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ApplicationFactoryTest extends TestCase
{
    public function test_make()
    {
        $factory = new ApplicationFactory($this->mockBootstrapper(), 2);

        $app1 = $factory->make();

        $this->assertInstanceOf(Application::class, $app1);
        $this->assertTrue(Facade::getFacadeApplication() === $app1);
        $this->assertTrue(Container::getInstance() === $app1);
        $this->assertEquals(1, $factory->getPool()->length());

        $app2 = $factory->make();

        $this->assertInstanceOf(Application::class, $app2);
        $this->assertTrue(Facade::getFacadeApplication() === $app2);
        $this->assertTrue(Container::getInstance() === $app2);
        $this->assertEquals(0, $factory->getPool()->length());
    }


    public function test_recycle()
    {
        $factory = new ApplicationFactory($this->mockBootstrapper(), 2);

        $app = $factory->make();

        $factory->recycle($app);

        $this->assertEquals(2, $factory->getPool()->length());
    }


    protected function mockBootstrapper()
    {
        $bootstrapper = m::mock(FrameworkBootstrapper::class);

        $bootstrapper->shouldReceive('boot')->times(2)->andReturns(new Application, new Application);

        return $bootstrapper;
    }
}
