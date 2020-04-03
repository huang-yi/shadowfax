<?php

namespace HuangYi\Shadowfax\Contracts;

interface Task
{
    /**
     * Handle the task.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $taskId
     * @param  int  $fromWorkerId
     * @param  int  $flags
     * @return mixed
     */
    public function handle($server, $taskId, $fromWorkerId, $flags);
}
