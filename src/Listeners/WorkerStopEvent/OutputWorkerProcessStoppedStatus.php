<?php

namespace HuangYi\Shadowfax\Listeners\WorkerStopEvent;

use HuangYi\Shadowfax\Events\WorkerStopEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class OutputWorkerProcessStoppedStatus
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\WorkerStopEvent  $event
     * @return void
     */
    public function handle(WorkerStopEvent $event)
    {
        $workerName = $this->getWorkerName($event->server, $event->workerId);

        $this->output(
            "<info>[×] {$workerName} process stopped.</info> <comment>[{$event->server->worker_pid}]</comment>"
        );
    }
}
