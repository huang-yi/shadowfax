<?php

namespace HuangYi\Shadowfax\Listeners\ManagerStartEvent;

use HuangYi\Shadowfax\Events\WorkerStartEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class SetManagerProcessName
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\WorkerStartEvent  $event
     * @return void
     */
    public function handle(WorkerStartEvent $event)
    {
        @swoole_set_process_name(sprintf(
            '%s: manager process%s',
            $this->getName(),
            $this->getHostAndPortString($event->server)
        ));
    }
}
