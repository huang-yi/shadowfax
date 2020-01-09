<?php

namespace HuangYi\Shadowfax\Factories;

use HuangYi\Shadowfax\Contracts\AppFactory as AppFactoryContract;
use HuangYi\Shadowfax\FrameworkBootstrapper;
use Illuminate\Contracts\Container\Container;

class AppFactory implements AppFactoryContract
{
    /**
     * The Laravel/Lumen application.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * AppFactory constructor.
     *
     * @param  \HuangYi\Shadowfax\FrameworkBootstrapper  $bootstrapper
     * @return void
     */
    public function __construct(FrameworkBootstrapper $bootstrapper)
    {
        $this->app = $bootstrapper->boot();
    }

    /**
     * Make a Laravel/Lumen application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function make(): Container
    {
        return $this->app;
    }

    /**
     * Recycle the Laravel/Lumen application.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function recycle(Container $app)
    {
        return;
    }
}
