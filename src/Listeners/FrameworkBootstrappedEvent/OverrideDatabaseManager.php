<?php

namespace HuangYi\Shadowfax\Listeners\FrameworkBootstrappedEvent;

use HuangYi\Shadowfax\Events\FrameworkBootstrappedEvent;
use HuangYi\Shadowfax\Laravel\DatabaseManager;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class OverrideDatabaseManager
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\FrameworkBootstrappedEvent  $event
     * @return void
     */
    public function handle(FrameworkBootstrappedEvent $event)
    {
        $event->app->singleton('db', function ($app) {
            return new DatabaseManager($app, $app['db.factory'], $this->config('db_pools', []));
        });
    }
}
