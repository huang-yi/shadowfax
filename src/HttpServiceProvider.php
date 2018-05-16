<?php

namespace HuangYi\Swoole\Http;

use HuangYi\Swoole\Http\Console\HttpServerCommand;
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
     * Register manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('swoole.http', function ($app) {
            return new ServerManager($app, $app['config']['http']);
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
