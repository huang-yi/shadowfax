<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use HuangYi\Shadowfax\Exceptions\InvalidFrameworkBootstrapperException;
use HuangYi\Shadowfax\Laravel\FrameworkBootstrapper;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application as Laravel;
use Laravel\Lumen\Application as Lumen;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

class FrameworkBootstrapperTest extends TestCase
{
    public function tearDown(): void
    {
        Container::setInstance(null);
    }


    public function testBootstrapLaravel()
    {
        $bootstrapper = new FrameworkBootstrapper(__DIR__.'/../frameworks/laravel/bootstrap/app.php');

        $app = $bootstrapper->bootstrap();

        $this->assertInstanceOf(Laravel::class, $app);
        $this->assertTrue($app->hasBeenBootstrapped());
    }


    public function testBootstrapLumen()
    {
        $bootstrapper = new FrameworkBootstrapper(__DIR__.'/../frameworks/lumen/bootstrap/app.php');

        $app = $bootstrapper->bootstrap();

        $this->assertInstanceOf(Lumen::class, $app);

        $booted = (new ReflectionObject($app))->getProperty('booted');

        $booted->setAccessible(true);

        $this->assertTrue($booted->getValue($app));
    }


    public function testBootstrapWithInvalidPath()
    {
        $this->expectException(InvalidFrameworkBootstrapperException::class);

        $bootstrapper = new FrameworkBootstrapper('invalid/path');

        $bootstrapper->bootstrap();
    }
}
