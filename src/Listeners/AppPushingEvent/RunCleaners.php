<?php

namespace HuangYi\Shadowfax\Listeners\AppPushingEvent;

use HuangYi\Shadowfax\Events\AppPushingEvent;
use HuangYi\Shadowfax\Laravel\CleanersRunner;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class RunCleaners
{
    use HasHelpers;

    /**
     * The cleaners runner instance.
     *
     * @var \HuangYi\Shadowfax\Laravel\CleanersRunner
     */
    protected static $runner;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\AppPushingEvent  $event
     * @return void
     */
    public function handle(AppPushingEvent $event)
    {
        $this->initialize();

        static::$runner->run($event->app);
    }

    /**
     * Initialize the instance.
     *
     * @return void
     */
    protected function initialize()
    {
        if (! is_null(static::$runner)) {
            return;
        }

        $cleaners = array_merge(
            (array) $this->config('cleaners', []),
            ['app/Cleaners/']
        );

        static::$runner = new CleanersRunner(
            $cleaners,
            app()->getNamespace(),
            app()->path()
        );
    }
}
