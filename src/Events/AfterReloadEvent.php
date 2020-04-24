<?php

namespace HuangYi\Shadowfax\Events;

class AfterReloadEvent
{
    /**
     * The Swoole server instance.
     *
     * @var \Swoole\Server
     */
    public $server;

    /**
     * Create a new AfterReloadEvent instance.
     *
     * @param  \Swoole\Server  $server
     * @return void
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}
