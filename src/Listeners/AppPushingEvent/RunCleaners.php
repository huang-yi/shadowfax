<?php

namespace HuangYi\Shadowfax\Listeners\AppPushingEvent;

use HuangYi\Shadowfax\Contracts\Cleaner;
use HuangYi\Shadowfax\Events\AppPushingEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class RunCleaners
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\AppPushingEvent  $event
     * @return void
     */
    public function handle(AppPushingEvent $event)
    {
        $cleaners = $this->config('cleaners', []);

        foreach ($cleaners as $cleaner) {
            if (is_subclass_of($cleaner, Cleaner::class)) {
                (new $cleaner)->clean($event->app);
            }
        }
    }
}
