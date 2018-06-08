<?php

namespace HuangYi\Swoole;

use HuangYi\Swoole\Console\ServerCommand;
use HuangYi\Swoole\Websocket\Middleware\JoinNamespace;
use HuangYi\Swoole\Websocket\Message\Router as MessageRouter;
use HuangYi\Swoole\Websocket\NamespaceManager;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router as HttpRouter;
use Illuminate\Support\ServiceProvider;

class SwooleServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerWebsocket();
        $this->registerManager();
        $this->registerCommands();
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/swoole.php' => base_path('config/swoole.php')
        ], 'config');
    }

    /**
     * Merge configurations.
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/swoole.php', 'swoole');
    }

    /**
     * Register websocket.
     *
     * @return void
     */
    protected function registerWebsocket()
    {
        $this->app->singleton('swoole.websocket.router', function ($app) {
            return new HttpRouter($app['events'], $app);
        });

        $this->app->singleton('swoole.websocket.kernel', function ($app) {
            $class = get_class($app[Kernel::class]);

            $kernel = new $class($app, $app['swoole.websocket.router']);

            $kernel->pushMiddleware(JoinNamespace::class);

            return $kernel;
        });

        $this->app->singleton('swoole.websocket.namespace', function ($app) {
            return new NamespaceManager($app);
        });

        $this->app->singleton('swoole.websocket.message.router', function ($app) {
            return new MessageRouter($app);
        });
    }

    /**
     * Register manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('swoole.server', function ($app) {
            return new ServerManager($app);
        });

        $this->app['events']->listen('swoole.requested', function () {
            $this->resetProviders();
        });

        $this->app['events']->listen('swoole.opened', function () {
            $this->resetProviders();
        });
    }

    /**
     * Reset providers.
     *
     * @return void
     */
    protected function resetProviders()
    {
        $resetProviders = $this->app['config']->get('swoole.server.reset_providers');

        foreach ($resetProviders as $provider) {
            if (is_subclass_of($provider, ServiceProvider::class)) {
                $this->app->register($provider, [], true);
            }
        }
    }

    /**
     * Register commands.
     */
    protected function registerCommands()
    {
        $this->commands([
            ServerCommand::class,
        ]);
    }
}
