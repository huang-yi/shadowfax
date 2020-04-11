<?php

namespace HuangYi\Shadowfax\Listeners\WorkerStartEvent;

use HuangYi\Shadowfax\Events\WorkerStartEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class OutputWorkerProcessStartedStatus
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
        $workerName = $this->getWorkerName($event->server, $event->workerId);

        $this->output(
            "<info>[√] $workerName process started.</info> <comment>[{$event->server->worker_pid}]</comment>"
        );
    }
}
