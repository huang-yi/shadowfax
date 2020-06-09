<?php

namespace HuangYi\Shadowfax\Laravel;

use HuangYi\Shadowfax\Contracts\EventDispatcher;
use HuangYi\Shadowfax\Events\FrameworkBootstrappedEvent;
use HuangYi\Shadowfax\Exceptions\InvalidFrameworkBootstrapperException;
use HuangYi\Shadowfax\HasEventDispatcher;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application as Laravel;
use Illuminate\Http\Request;
use Laravel\Lumen\Application as Lumen;

class FrameworkBootstrapper
{
    use HasEventDispatcher;

    /**
     * The application bootstrap path.
     *
     * @var string
     */
    protected $bootstrapFile;

    /**
     * Indicates whether to bootstrap a console kernel application.
     *
     * @var bool
     */
    protected $isConsoleKernel;

    /**
     * Create a new FrameworkBootstrapper instance.
     *
     * @param  string  $bootstrapFile
     * @param  bool  $isConsoleKernel
     * @param  \HuangYi\Shadowfax\Contracts\EventDispatcher  $events
     * @return void
     */
    public function __construct($bootstrapFile, $isConsoleKernel = false, EventDispatcher $events = null)
    {
        $this->bootstrapFile = $bootstrapFile;
        $this->isConsoleKernel = $isConsoleKernel;
        $this->events = $events;
    }

    /**
     * Bootstrap a Laravel/Lumen application.
     *
     * @return \Illuminate\Contracts\Container\Container
     * @throws \HuangYi\Shadowfax\Exceptions\InvalidFrameworkBootstrapperException
     */
    public function bootstrap(): Container
    {
        $app = $this->createApplication();

        $this->bootstrapApplication($app);

        $this->dispatch(FrameworkBootstrappedEvent::class, $app);

        return $app;
    }

    /**
     * Create the application.
     *
     * @return \Illuminate\Contracts\Container\Container
     * @throws \HuangYi\Shadowfax\Exceptions\InvalidFrameworkBootstrapperException
     */
    protected function createApplication()
    {
        if (! file_exists($this->bootstrapFile)) {
            throw new InvalidFrameworkBootstrapperException($this->bootstrapFile);
        }

        $app = require $this->bootstrapFile;

        if (! $app instanceof Container) {
            throw new InvalidFrameworkBootstrapperException($this->bootstrapFile);
        }

        return $app;
    }

    /**
     * Bootstrap the application.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    protected function bootstrapApplication(Container $app)
    {
        if ($app instanceof Laravel) {
            $this->bootstrapLaravelKernel($app);
        } elseif ($app instanceof Lumen && method_exists($app, 'boot')) {
            $app->boot();
        }
    }

    /**
     * Bootstrap the Laravel kernel.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function bootstrapLaravelKernel(Laravel $app)
    {
        if ($this->isConsoleKernel) {
            $app->make(ConsoleKernel::class)->bootstrap();
        } else {
            $app->instance('request', Request::create('http://localhost'));

            $app->make(HttpKernel::class)->bootstrap();
        }
    }
}
