<?php

namespace HuangYi\Shadowfax\Server\Events;

use HuangYi\Shadowfax\Contracts\Task;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Throwable;

class TaskEvent extends Event
{
    /**
     * Handle the event.
     *
     * @param  mixed  ...$args
     * @return void
     */
    public function handle(...$args)
    {
        if (! $args[3] instanceof Task) {
            return;
        }

        $app = $this->appFactory()->make();

        try {
            $args[3]->handle($args[0], $args[2], $args[2]);
        } catch (Throwable $e) {
            $app[ExceptionHandler::class]->report($e);
        }

        $this->appFactory()->recycle($app);
    }
}
