<?php

namespace HuangYi\Shadowfax;

use HuangYi\Shadowfax\Console\CleanerCommand;
use HuangYi\Shadowfax\Console\HandlerCommand;
use HuangYi\Shadowfax\Console\PublishCommand;
use HuangYi\Shadowfax\Console\TaskCommand;
use HuangYi\Shadowfax\WebSocket\LaravelRouter;
use HuangYi\Shadowfax\WebSocket\LumenRouter;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as Lumen;

class ShadowfaxServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->runInShadowfax()) {
            $this->registerTaskDispatcher();

            $this->registerWebSocket();
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanerCommand::class,
                HandlerCommand::class,
                PublishCommand::class,
                TaskCommand::class,
            ]);
        }
    }

    /**
     * Register the task dispatcher.
     *
     * @return void
     */
    protected function registerTaskDispatcher()
    {
        $this->app->singleton('shadowfax.task', function () {
            return new TaskDispatcher();
        });

        $this->app->alias('shadowfax.task', TaskDispatcher::class);
    }

    /**
     * Register the websocket router.
     *
     * @return void
     */
    protected function registerWebSocket()
    {
        $this->app->singleton('shadowfax.websocket', function ($app) {
            if ($app instanceof Lumen) {
                return new LumenRouter($app);
            }

            return new LaravelRouter($app['events'], $app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../.watch' => base_path('.watch'),
                __DIR__.'/../shadowfax' => base_path('shadowfax'),
                __DIR__.'/../shadowfax.yml' => base_path('shadowfax.yml.example'),
                __DIR__.'/../bootstrap/shadowfax.php' => base_path('bootstrap/shadowfax.php'),
            ], 'shadowfax');
        }
    }

    /**
     * Determine if the application is running in the Shadowfax process.
     *
     * @return bool
     */
    protected function runInShadowfax()
    {
        return defined('SHADOWFAX_START');
    }
}
