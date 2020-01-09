<?php

namespace HuangYi\Shadowfax\Tests\Factories;

use HuangYi\Shadowfax\Factories\AppFactory;
use HuangYi\Shadowfax\FrameworkBootstrapper;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class AppFactoryTest extends TestCase
{
    public function test_make()
    {
        $factory = new AppFactory($this->mockFrameworkBootstrapper());

        $this->assertInstanceOf(Application::class, $factory->make());
    }


    protected function mockFrameworkBootstrapper()
    {
        $bootstrapper = m::mock(FrameworkBootstrapper::class);

        $bootstrapper->shouldReceive('boot')->once()->andReturn(new Application);

        return $bootstrapper;
    }
}
