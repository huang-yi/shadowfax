<?php

namespace HuangYi\Shadowfax\Events;

class FinishEvent
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
     * The task result.
     *
     * @var mixed
     */
    public $result;

    /**
     * Create a new FinishEvent instance.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $taskId
     * @param  mixed  $result
     * @return void
     */
    public function __construct($server, $taskId, $result)
    {
        $this->server = $server;
        $this->taskId = $taskId;
        $this->result = $result;
    }
}
