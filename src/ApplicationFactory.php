<?php

namespace HuangYi\Shadowfax;

use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class ApplicationFactory
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
     * The application pool.
     *
     * @var \Swoole\Coroutine\Channel
     */
    protected $pool;

    /**
     * The application bootstrap file.
     *
     * @var string
     */
    protected $path;

    /**
     * The application kernel type.
     *
     * @var int
     */
    protected $type;

    /**
     * The pool capacity.
     *
     * @var int
     */
    protected $capacity;

    /**
     * ApplicationFactory constructor.
     *
     * @param  string  $path
     * @param  int  $type
     * @param  int  $capacity
     * @return void
     */
    public function __construct($path, $type, $capacity = 10)
    {
        $this->path = $path;
        $this->type = $type;
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
            $this->pool->push($this->createApplication());
        }
    }

    /**
     * Create an application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    protected function createApplication()
    {
        if ($this->type == static::TYPE_CONSOLE) {
            return $this->createConsoleApplication();
        }

        return $this->createHttpApplication();
    }

    /**
     * Create the console kernel application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    protected function createConsoleApplication()
    {
        $app = require $this->path;

        if ($app instanceof Application) {
            $app->make(ConsoleKernel::class)->bootstrap();
        }

        return $app;
    }

    /**
     * Create the http kernel application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    protected function createHttpApplication()
    {
        $app = require $this->path;

        $app->instance('request', Request::create('http://localhost'));

        if ($app instanceof Application) {
            $app->make(HttpKernel::class)->bootstrap();
        }

        return $app;
    }

    /**
     * Make a Laravel/Lumen application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function make()
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
     * Get the application bootstrap file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the application kernel type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
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
