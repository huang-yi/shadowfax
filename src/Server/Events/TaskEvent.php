<?php

namespace HuangYi\Shadowfax\Server\Events;

use HuangYi\Shadowfax\Contracts\Task;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Swoole\Server\Task as SwooleTask;
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
        if ($args[1] instanceof SwooleTask) {
            return $this->handleCoroutineTask(...$args);
        }

        if (! $args[3] instanceof Task) {
            return;
        }

        $app = $this->appFactory()->make();

        try {
            $args[3]->handle($args[0], $args[1], $args[2]);
        } catch (Throwable $e) {
            $app[ExceptionHandler::class]->report($e);
        }

        $this->appFactory()->recycle($app);
    }

    /**
     * Handle the coroutine task.
     *
     * @param  mixed  ...$args
     * @return void
     */
    protected function handleCoroutineTask(...$args)
    {
        if (! $args[1]->data instanceof Task) {
            return;
        }

        $app = $this->appFactory()->make();

        try {
            $args[1]->data->handle(
                $args[0],
                $args[1]->id,
                $args[1]->worker_id,
                $args[1]->flags
            );
        } catch (Throwable $e) {
            $app[ExceptionHandler::class]->report($e);
        }

        $this->appFactory()->recycle($app);
    }
}
