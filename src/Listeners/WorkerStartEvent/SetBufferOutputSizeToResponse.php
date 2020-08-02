<?php

namespace HuangYi\Shadowfax\Listeners\WorkerStartEvent;

use HuangYi\Shadowfax\Events\WorkerStartEvent;
use HuangYi\Shadowfax\Http\Response;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class SetBufferOutputSizeToResponse
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
        Response::setBufferOutputSize(
            $event->server->setting['buffer_output_size'] ?? (2 * 1024 * 1024)
        );
    }
}
