<?php

namespace HuangYi\Shadowfax;

use HuangYi\Shadowfax\Task\Dispatcher;
use Illuminate\Support\ServiceProvider;

class ShadowfaxServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/shadowfax.php', 'shadowfax'
        );

        $this->registerShadowfax();
        $this->registerTaskDispatcher();
    }

    /**
     * Register the shadowfax.
     *
     * @return void
     */
    protected function registerShadowfax()
    {
        $this->app->instance('shadowfax', Shadowfax::getInstance());
        $this->app->alias('shadowfax', Shadowfax::class);
    }

    /**
     * Register the task dispatcher.
     *
     * @return void
     */
    protected function registerTaskDispatcher()
    {
        $this->app->singleton('shadowfax.task', function () {
            return new Dispatcher;
        });

        $this->app->alias('shadowfax.task', Dispatcher::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../shadowfax.ini' => base_path('shadowfax.ini'),
            __DIR__.'/../config/shadowfax.php' => base_path('config/shadowfax.php'),
        ]);
    }
}
