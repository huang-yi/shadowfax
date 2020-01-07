<?php

namespace HuangYi\Shadowfax;

use Illuminate\Contracts\Console\Kernel as LaravelConsoleKernel;
use Illuminate\Contracts\Http\Kernel as LaravelHttpKernel;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Http\Request;
use Laravel\Lumen\Application as LumenApplication;

class FrameworkBootstrapper
{
    /**
     * The HTTP type.
     */
    const TYPE_HTTP = 1;

    /**
     * The console type.
     */
    const TYPE_CONSOLE = 2;

    /**
     * The bootstrap file.
     *
     * @var string
     */
    protected $path;

    /**
     * The kernel type.
     *
     * @var int
     */
    protected $type;

    /**
     * FrameworkBootstrapper constructor.
     *
     * @param  string  $path
     * @param  string  $type
     * @return void
     */
    public function __construct($path, $type)
    {
        $this->path = $path;
        $this->type = $type;
    }

    /**
     * Boot a Laravel/Lumen application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function boot()
    {
        if ($this->type == static::TYPE_CONSOLE) {
            return $this->bootConsoleApplication();
        }

        return $this->bootHttpApplication();
    }

    /**
     * Boot a console kernel application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    protected function bootConsoleApplication()
    {
        $app = require $this->path;

        if ($app instanceof LaravelApplication) {
            $app->make(LaravelConsoleKernel::class)->bootstrap();
        } elseif ($app instanceof LumenApplication) {
            $app->boot();
        }

        return $app;
    }

    /**
     * Boot a http kernel application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    protected function bootHttpApplication()
    {
        $app = require $this->path;

        $app->instance('request', Request::create('http://localhost'));

        if ($app instanceof LaravelApplication) {
            $app->make(LaravelHttpKernel::class)->bootstrap();
        } elseif ($app instanceof LumenApplication) {
            $app->boot();
        }

        return $app;
    }
}
