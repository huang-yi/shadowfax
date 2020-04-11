<?php

namespace HuangYi\Shadowfax\Listeners\ManagerStartEvent;

use HuangYi\Shadowfax\Events\ManagerStartEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class OutputManagerProcessStartedStatus
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\ManagerStartEvent  $event
     * @return void
     */
    public function handle(ManagerStartEvent $event)
    {
        $this->output(
            "<info>[√] manager process started.</info> <comment>[{$event->server->manager_pid}]</comment>"
        );
    }
}
