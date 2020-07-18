<?php

namespace HuangYi\Shadowfax\Laravel;

use HuangYi\Shadowfax\Contracts\AppPool;
use HuangYi\Shadowfax\Events\AppPoppedEvent;
use HuangYi\Shadowfax\Events\AppPushingEvent;
use HuangYi\Shadowfax\HasEventDispatcher;
use Illuminate\Contracts\Container\Container;
use Swoole\Coroutine\Channel;

class CoroutineAppPool implements AppPool
{
    use HasEventDispatcher;
    use RebindsAbstracts;

    /**
     * The framework bootstrapper.
     *
     * @var \HuangYi\Shadowfax\Laravel\FrameworkBootstrapper
     */
    protected $bootstrapper;

    /**
     * The abstracts need to be rebound.
     *
     * @var array
     */
    protected $abstracts;

    /**
     * The app pool capacity.
     *
     * @var int
     */
    protected $capacity;

    /**
     * The Swoole Coroutine Channel instance.
     *
     * @var \Swoole\Coroutine\Channel
     */
    protected $channel;

    /**
     * Create a new AppFactory instance.
     *
     * @param  \HuangYi\Shadowfax\Laravel\FrameworkBootstrapper  $bootstrapper
     * @param  array  $abstracts
     * @param  int  $capacity
     * @return void
     * @throws \HuangYi\Shadowfax\Exceptions\InvalidFrameworkBootstrapperException
     */
    public function __construct(FrameworkBootstrapper $bootstrapper, array $abstracts = [], $capacity = 10)
    {
        $this->bootstrapper = $bootstrapper;
        $this->abstracts = $abstracts;
        $this->capacity = $capacity;

        $this->initPool();
    }

    /**
     * Initialize the app pool.
     *
     * @return void
     * @throws \HuangYi\Shadowfax\Exceptions\InvalidFrameworkBootstrapperException
     */
    protected function initPool()
    {
        $this->channel = new Channel($this->capacity);

        for ($i = 0; $i < $this->capacity; $i++) {
            $this->channel->push($this->bootstrapper->bootstrap());
        }
    }

    /**
     * Pop a Laravel/Lumen application instance from the pool.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function pop(): Container
    {
        $app = $this->channel->pop();

        shadowfax_set_global_container($app);

        $this->dispatch(AppPoppedEvent::class, $app);

        return $app;
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

        $this->channel->push($app);
    }

    /**
     * Get the Swoole Coroutine Channel instance.
     *
     * @return \Swoole\Coroutine\Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
