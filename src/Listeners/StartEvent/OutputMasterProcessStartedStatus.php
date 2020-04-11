<?php

namespace HuangYi\Shadowfax\Listeners\StartEvent;

use HuangYi\Shadowfax\Events\StartEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class OutputMasterProcessStartedStatus
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\StartEvent  $event
     * @return void
     */
    public function handle(StartEvent $event)
    {
        $this->output(
            "<info>[√] master process started.</info> <comment>[{$event->server->master_pid}]</comment>"
        );
    }
}
