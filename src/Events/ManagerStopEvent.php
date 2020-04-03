<?php

namespace HuangYi\Shadowfax\Events;

class ManagerStopEvent
{
    /**
     * The Swoole server instance.
     *
     * @var \Swoole\Server
     */
    public $server;

    /**
     * Create a new ManagerStopEvent instance.
     *
     * @param  \Swoole\Server  $server
     * @return void
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}
