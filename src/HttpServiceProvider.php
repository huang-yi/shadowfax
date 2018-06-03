<?php

namespace HuangYi\Http;

use HuangYi\Http\Console\HttpServerCommand;
use HuangYi\Http\Websocket\Middleware\JoinNamespace;
use HuangYi\Http\Websocket\Message\Router as MessageRouter;
use HuangYi\Http\Websocket\NamespaceManager;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router as HttpRouter;
use Illuminate\Support\ServiceProvider;

class HttpServiceProvider extends ServiceProvider
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
            __DIR__ . '/../config/http.php' => base_path('config/http.php')
        ], 'config');
    }

    /**
     * Merge configurations.
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/http.php', 'http');
    }

    /**
     * Register websocket.
     *
     * @return void
     */
    protected function registerWebsocket()
    {
        if (! $this->app['config']->get('http.enable_websocket', false)) {
            return;
        }

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
    }

    /**
     * Register commands.
     */
    protected function registerCommands()
    {
        $this->commands([
            HttpServerCommand::class,
        ]);
    }
}
