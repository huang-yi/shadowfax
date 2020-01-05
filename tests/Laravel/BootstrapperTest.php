<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use HuangYi\Shadowfax\Laravel\Bootstrapper;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application as Laravel;
use Laravel\Lumen\Application as Lumen;
use PHPUnit\Framework\TestCase;

class BootstrapperTest extends TestCase
{
    public function test_boot_http_kernel_laravel()
    {
        $bootstrapper = new Bootstrapper(__DIR__.'/../frameworks/laravel/bootstrap/app.php');

        /** @var Laravel $app */
        $app = $bootstrapper->http();

        $this->assertInstanceOf(Laravel::class, $app);
        $this->assertTrue($app->has(HttpKernel::class));
        $this->assertTrue($app->isBooted());
    }


    public function test_boot_console_kernel_laravel()
    {
        $bootstrapper = new Bootstrapper(__DIR__.'/../frameworks/laravel/bootstrap/app.php');

        /** @var Laravel $app */
        $app = $bootstrapper->console();

        $this->assertInstanceOf(Laravel::class, $app);
        $this->assertTrue($app->has(ConsoleKernel::class));
        $this->assertTrue($app->isBooted());
    }


    public function test_boot_http_kernel_lumen()
    {
        $bootstrapper = new Bootstrapper(__DIR__.'/../frameworks/lumen/bootstrap/app.php');

        /** @var Lumen $app */
        $app = $bootstrapper->http();

        $this->assertInstanceOf(Lumen::class, $app);
    }


    public function test_boot_console_kernel_lumen()
    {
        $bootstrapper = new Bootstrapper(__DIR__.'/../frameworks/lumen/bootstrap/app.php');

        /** @var Lumen $app */
        $app = $bootstrapper->console();

        $this->assertInstanceOf(Lumen::class, $app);
    }
}
