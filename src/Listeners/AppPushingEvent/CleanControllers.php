<?php

namespace HuangYi\Shadowfax\Listeners\AppPushingEvent;

use HuangYi\Shadowfax\Events\AppPushingEvent;
use HuangYi\Shadowfax\Laravel\ControllersCleaner;
use HuangYi\Shadowfax\Listeners\HasHelpers;
use Laravel\Lumen\Application as Lumen;

class CleanControllers
{
    use HasHelpers;

    /**
     * The configured controllers.
     *
     * @var \HuangYi\Shadowfax\Laravel\ControllersCleaner
     */
    protected static $cleaner;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\AppPushingEvent  $event
     * @return void
     */
    public function handle(AppPushingEvent $event)
    {
        if ($event->app instanceof Lumen) {
            return;
        }

        $this->initialize();

        static::$cleaner->clean($event->app);
    }

    /**
     * Initialize the configuration.
     *
     * @return void
     */
    protected function initialize()
    {
        if (! is_null(static::$cleaner)) {
            return;
        }

        static::$cleaner = new ControllersCleaner(
            (array) $this->config('controllers', ['*'])
        );
    }
}
