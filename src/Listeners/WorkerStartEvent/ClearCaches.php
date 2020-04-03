<?php

namespace HuangYi\Shadowfax\Listeners\WorkerStartEvent;

use HuangYi\Shadowfax\Events\WorkerStartEvent;

class ClearCaches
{
    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\WorkerStartEvent  $event
     * @return void
     */
    public function handle(WorkerStartEvent $event)
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
    }
}
