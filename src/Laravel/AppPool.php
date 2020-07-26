<?php

namespace HuangYi\Shadowfax\Laravel;

use HuangYi\Shadowfax\Contracts\AppPool as PoolContract;
use HuangYi\Shadowfax\Events\AppPoppedEvent;
use HuangYi\Shadowfax\Events\AppPushingEvent;
use HuangYi\Shadowfax\HasEventDispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Facade;

class AppPool implements PoolContract
{
    use HasEventDispatcher;
    use RebindsAbstracts;

    /**
     * The Laravel/Lumen application.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * The abstracts need to be rebound.
     *
     * @var array
     */
    protected $abstracts;

    /**
     * Create a new AppFactory instance.
     *
     * @param  \HuangYi\Shadowfax\Laravel\FrameworkBootstrapper  $bootstrapper
     * @param  array  $abstracts
     * @return void
     * @throws \HuangYi\Shadowfax\Exceptions\InvalidFrameworkBootstrapperException
     */
    public function __construct(FrameworkBootstrapper $bootstrapper, array $abstracts = [])
    {
        $this->app = $bootstrapper->bootstrap();
        $this->abstracts = $abstracts;
    }

    /**
     * Pop a Laravel/Lumen application instance from the pool.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function pop(): Container
    {
        Facade::clearResolvedInstances();

        $this->dispatch(AppPoppedEvent::class, $this->app);

        return $this->app;
    }

    /**
     * Push a Laravel/Lumen application to the pool.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function push(Container $app)
    {
        $this->rebindAbstracts($app, $this->abstracts);

        $this->dispatch(AppPushingEvent::class, $app);
    }
}
