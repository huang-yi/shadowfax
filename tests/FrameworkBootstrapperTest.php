<?php

namespace HuangYi\Shadowfax\Tests;

use HuangYi\Shadowfax\FrameworkBootstrapper;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use PHPUnit\Framework\TestCase;

class FrameworkBootstrapperTest extends TestCase
{
    public function test_boot_laravel_http_application()
    {
        $bootstrapper = new FrameworkBootstrapper(
            __DIR__.'/frameworks/laravel/bootstrap/app.php',
            FrameworkBootstrapper::TYPE_HTTP
        );

        $app = $bootstrapper->boot();

        $this->assertInstanceOf(LaravelApplication::class, $app);
        $this->assertTrue($app->bound(HttpKernel::class));
        $this->assertTrue($app->isBooted());
    }


    public function test_boot_laravel_console_application()
    {
        $bootstrapper = new FrameworkBootstrapper(
            __DIR__.'/frameworks/laravel/bootstrap/app.php',
            FrameworkBootstrapper::TYPE_CONSOLE
        );

        $app = $bootstrapper->boot();

        $this->assertInstanceOf(LaravelApplication::class, $app);
        $this->assertTrue($app->bound(ConsoleKernel::class));
        $this->assertTrue($app->isBooted());
    }


    public function test_boot_lumen_http_application()
    {
        $bootstrapper = new FrameworkBootstrapper(
            __DIR__.'/frameworks/lumen/bootstrap/app.php',
            FrameworkBootstrapper::TYPE_HTTP
        );

        $app = $bootstrapper->boot();

        $this->assertInstanceOf(LumenApplication::class, $app);
    }


    public function test_boot_lumen_console_application()
    {
        $bootstrapper = new FrameworkBootstrapper(
            __DIR__.'/frameworks/lumen/bootstrap/app.php',
            FrameworkBootstrapper::TYPE_CONSOLE
        );

        $app = $bootstrapper->boot();

        $this->assertInstanceOf(LumenApplication::class, $app);
        $this->assertTrue($app->bound(ConsoleKernel::class));
    }
}
