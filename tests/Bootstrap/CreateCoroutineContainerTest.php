<?php

namespace HuangYi\Shadowfax\Tests\Bootstrap;

use HuangYi\Shadowfax\Bootstrap\CreateCoroutineContainer;
use HuangYi\Shadowfax\Shadowfax;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CreateCoroutineContainerTest extends TestCase
{
    public function tearDown(): void
    {
        @unlink((new CreateCoroutineContainer)->getOutput());
    }


    public function testCreateCoroutineContainer()
    {
        $bootstrapper = new CreateCoroutineContainer;

        $bootstrapper->createCoroutineContainer($this->mockShadowfax());

        $this->assertFileExists($bootstrapper->getOutput());
        $this->assertStringContainsString('shadowfax_correct_container(static::$instance);', file_get_contents($bootstrapper->getOutput()));
    }


    protected function mockShadowfax()
    {
        $shadowfax = m::mock(Shadowfax::class);

        $shadowfax->shouldReceive('basePath')
            ->once()
            ->with('vendor/laravel/framework/src/Illuminate/Container/Container.php')
            ->andReturn(__DIR__.'/../../vendor/laravel/framework/src/Illuminate/Container/Container.php');

        return $shadowfax;
    }
}
