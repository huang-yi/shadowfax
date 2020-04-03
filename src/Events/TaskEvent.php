<?php

namespace HuangYi\Shadowfax\Events;

use Swoole\Server\Task as SwooleTask;

class TaskEvent
{
    /**
     * The Swoole server instance.
     *
     * @var \Swoole\Server
     */
    public $server;

    /**
     * The task id.
     *
     * @var int
     */
    public $taskId;

    /**
     * The worker id.
     *
     * @var int
     */
    public $fromWorkerId;

    /**
     * The task flags.
     *
     * @var int
     */
    public $flags;

    /**
     * The task instance.
     *
     * @var \HuangYi\Shadowfax\Contracts\Task
     */
    public $task;

    /**
     * Create a new TaskEvent instance.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $taskId
     * @param  int  $fromWorkerId
     * @param  \HuangYi\Shadowfax\Contracts\Task  $task
     * @return void
     */
    public function __construct($server, $taskId, $fromWorkerId = null, $task = null)
    {
        $this->server = $server;

        if ($taskId instanceof SwooleTask) {
            $this->taskId = $taskId->id;
            $this->fromWorkerId = $taskId->worker_id;
            $this->flags = $taskId->flags;
            $this->task = $taskId->data;
        } else {
            $this->taskId = $taskId;
            $this->fromWorkerId = $fromWorkerId;
            $this->task = $task;
        }
    }
}
