<?php

namespace HuangYi\Shadowfax;

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
