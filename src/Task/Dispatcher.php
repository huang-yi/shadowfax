<?php

namespace HuangYi\Shadowfax\Task;

use HuangYi\Shadowfax\Contracts\Task;
use HuangYi\Shadowfax\Shadowfax;
use Swoole\Http\Server;

class Dispatcher
{
    /**
     * Dispatch a task to task worker process.
     *
     * @param  \HuangYi\Shadowfax\Contracts\Task  $task
     * @return $this
     */
    public function dispatch(Task $task)
    {
        Shadowfax::getInstance()->make(Server::class)->task($task);

        return $this;
    }
}
