<?php

namespace HuangYi\Shadowfax\Factories;

use HuangYi\Shadowfax\Contracts\AppFactory;
use HuangYi\Shadowfax\FrameworkBootstrapper;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Support\Facades\Facade;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class CoroutineAppFactory implements AppFactory
{
    /**
     * The application pool.
     *
     * @var \Swoole\Coroutine\Channel
     */
    protected $pool;

    /**
     * The application bootstrapper.
     *
     * @var \HuangYi\Shadowfax\FrameworkBootstrapper
     */
    protected $bootstrapper;

    /**
     * The pool capacity.
     *
     * @var int
     */
    protected $capacity;

    /**
     * CoroutineAppFactory constructor.
     *
     * @param  \HuangYi\Shadowfax\FrameworkBootstrapper  $bootstrapper
     * @param  int  $capacity
     * @return void
     */
    public function __construct(FrameworkBootstrapper $bootstrapper, $capacity = 10)
    {
        $this->bootstrapper = $bootstrapper;
        $this->capacity = $capacity;

        $this->init();
    }

    /**
     * Initialize the application factory.
     *
     * @return void
     */
    protected function init()
    {
        $this->pool = new Channel($this->capacity);

        for ($i = 0; $i < $this->capacity; $i++) {
            $this->pool->push($this->bootstrapper->boot());
        }
    }

    /**
     * Make a Laravel/Lumen application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function make(): ContainerContract
    {
        $app = $this->pool->pop();

        Coroutine::getContext()->laravel = $app;

        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        Facade::clearResolvedInstances();

        return $app;
    }

    /**
     * Recycle the Laravel/Lumen application.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function recycle(ContainerContract $app)
    {
        $this->rebindAbstracts($app);

        $this->pool->push($app);
    }

    /**
     * Rebind the application's abstracts.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    protected function rebindAbstracts(ContainerContract $app)
    {
        if (! $app->bound('config')) {
            return;
        }

        $resets = $app['config']['shadowfax.abstracts'] ?: [];

        foreach ($resets as $item) {
            if ($app->bound($item)) {
                static::rebindAbstract($app, $item);
            }
        }
    }

    /**
     * Rebind abstract.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @param  string  $name
     * @return void
     */
    protected function rebindAbstract(ContainerContract $app, $name)
    {
        $abstract = $app->getAlias($name);
        $binding = $app->getBindings()[$abstract] ?? null;

        unset($app[$abstract]);

        if ($binding) {
            $app->bind($abstract, $binding['concrete'], $binding['shared']);
        }
    }

    /**
     * Get the application pool.
     *
     * @return \Swoole\Coroutine\Channel
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * Get the pool capacity.
     *
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }
}
