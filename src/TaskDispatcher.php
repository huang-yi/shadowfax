<?php

namespace HuangYi\Shadowfax;

use HuangYi\Shadowfax\Contracts\Task;

class TaskDispatcher
{
    /**
     * Dispatch a task to task worker process.
     *
     * @param  \HuangYi\Shadowfax\Contracts\Task  $task
     * @return int
     */
    public function dispatch(Task $task)
    {
        return shadowfax('server')->task($task);
    }
}
