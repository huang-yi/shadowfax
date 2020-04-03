<?php

namespace HuangYi\Shadowfax\Listeners\WorkerStartEvent;

use HuangYi\Shadowfax\Events\WorkerStartEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class SetWorkerProcessName
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
            '%s: %s process%s',
            $this->getName(),
            $this->getWorkerName($event->server, $event->workerId),
            $this->getHostAndPortString($event->server)
        ));
    }
}
