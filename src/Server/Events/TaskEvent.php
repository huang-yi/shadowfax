<?php

namespace HuangYi\Shadowfax\Server\Events;

use Exception;
use HuangYi\Shadowfax\Contracts\Task;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
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
        } catch (Exception $e) {
            $app[ExceptionHandler::class]->report($e);
        } catch (Throwable $e) {
            $app[ExceptionHandler::class]->report(new FatalThrowableError($e));
        }

        $this->appFactory()->recycle($app);
    }
}
