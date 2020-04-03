<?php

namespace HuangYi\Shadowfax\Listeners\ShutdownEvent;

use HuangYi\Shadowfax\Events\ShutdownEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class OutputShutdownStatus
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\ShutdownEvent  $event
     * @return void
     */
    public function handle(ShutdownEvent $event)
    {
        $this->output("<info>[×] The Shadowfax server stopped.</info>");
    }
}
