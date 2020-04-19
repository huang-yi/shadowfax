<?php

namespace HuangYi\Shadowfax\Events;

class StartingEvent
{
    /**
     * The Swoole server instance.
     *
     * @var \Swoole\Server
     */
    public $server;

    /**
     * Create a new StartingEvent instance.
     *
     * @param  \Swoole\Server  $server
     * @return void
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}
