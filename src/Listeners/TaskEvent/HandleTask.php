<?php

namespace HuangYi\Shadowfax\Listeners\TaskEvent;

use HuangYi\Shadowfax\Contracts\Task;
use HuangYi\Shadowfax\Events\TaskEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class HandleTask
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\TaskEvent  $event
     * @return void
     */
    public function handle(TaskEvent $event)
    {
        if (! $event->task instanceof Task) {
            return;
        }

        $this->handleWithoutException(function ($app) use ($event) {
            $event->task->handle($event->server, $event->taskId, $event->fromWorkerId, $event->flags);
        });
    }
}
