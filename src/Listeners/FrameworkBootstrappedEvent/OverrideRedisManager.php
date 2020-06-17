<?php

namespace HuangYi\Shadowfax\Listeners\FrameworkBootstrappedEvent;

use HuangYi\Shadowfax\Events\FrameworkBootstrappedEvent;
use HuangYi\Shadowfax\Laravel\RedisManager;
use HuangYi\Shadowfax\Listeners\HasHelpers;
use Illuminate\Support\Arr;

class OverrideRedisManager
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
        if ($event->app->bound('redis')) {
            $event->app->singleton('redis', function ($app) {
                $config = $app->make('config')->get('database.redis', []);

                return new RedisManager(
                    $app,
                    Arr::pull($config, 'client', 'phpredis'),
                    $config,
                    $this->config('redis_pools', [])
                );
            });
        }
    }
}
