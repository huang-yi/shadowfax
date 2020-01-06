<?php

namespace HuangYi\Shadowfax;

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class Bootstrapper
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
     * The Laravel bootstrap path.
     *
     * @var string
     */
    protected $path;

    /**
     * Bootstrapper constructor.
     *
     * @param  string  $path
     * @return void
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Boot the HTTP Kernel application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function http()
    {
        $app = require $this->path;

        $app->instance('request', Request::create('http://localhost'));

        if ($app instanceof Application) {
            $app->make(HttpKernel::class)->bootstrap();
        }

        return $app;
    }

    /**
     * Boot the console kernel application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function console()
    {
        $app = require $this->path;

        if ($app instanceof Application) {
            $app->make(ConsoleKernel::class)->bootstrap();
        }

        return $app;
    }
}
