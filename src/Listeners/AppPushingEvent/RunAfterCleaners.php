<?php

namespace HuangYi\Shadowfax\Listeners\AppPushingEvent;

use HuangYi\Shadowfax\Laravel\Cleaners\PaginationCleaner;
use HuangYi\Shadowfax\Laravel\CleanersRunner;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class RunAfterCleaners
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
    public function handle($event)
    {
        $this->initialize();

        $this->callRunner($event->app);
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
            [
                'app/Cleaners/',
                PaginationCleaner::class,
            ]
        );

        static::$runner = new CleanersRunner(
            $cleaners,
            app()->getNamespace(),
            app()->path()
        );
    }

    /**
     * Call the runner.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    protected function callRunner($app)
    {
        static::$runner->runAfter($app);
    }
}
