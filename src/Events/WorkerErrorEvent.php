<?php

namespace HuangYi\Shadowfax\Events;

class WorkerErrorEvent
{
    /**
     * The Swoole server instance.
     *
     * @var \Swoole\Server
     */
    public $server;

    /**
     * The worker id.
     *
     * @var int
     */
    public $workerId;

    /**
     * The worker process id.
     *
     * @var int
     */
    public $workerPid;

    /**
     * The exit code.
     *
     * @var int
     */
    public $exitCode;

    /**
     * The signal.
     *
     * @var int
     */
    public $signal;

    /**
     * Create a new WorkerErrorEvent instance.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @param  int  $workerPid
     * @param  int  $exitCode
     * @param  int  $signal
     * @return void
     */
    public function __construct($server, $workerId, $workerPid, $exitCode, $signal)
    {
        $this->server = $server;
        $this->workerId = $workerId;
        $this->workerPid = $workerPid;
        $this->exitCode = $exitCode;
        $this->signal = $signal;
    }
}
