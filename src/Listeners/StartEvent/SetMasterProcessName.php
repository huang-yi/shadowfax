<?php

namespace HuangYi\Shadowfax\Listeners\StartEvent;

use HuangYi\Shadowfax\Events\StartEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class SetMasterProcessName
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
        @swoole_set_process_name(sprintf(
            '%s: master process %s:%d',
            $this->getName(),
            $event->server->host,
            $event->server->port
        ));
    }
}
