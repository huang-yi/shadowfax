<?php

namespace HuangYi\Shadowfax\Listeners\AppPoppedEvent;

use HuangYi\Shadowfax\Listeners\AppPushingEvent\RunAfterCleaners;

class RunBeforeCleaners extends RunAfterCleaners
{
    /**
     * Call the runner.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    protected function callRunner($app)
    {
        static::$runner->runBefore($app);
    }
}
