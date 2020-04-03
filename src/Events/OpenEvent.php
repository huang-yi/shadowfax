<?php

namespace HuangYi\Shadowfax\Events;

class OpenEvent
{
    /**
     * The WebSocket server instance.
     *
     * @var \Swoole\WebSocket\Server
     */
    public $server;

    /**
     * The HTTP request instance.
     *
     * @var \Swoole\Http\Request
     */
    public $request;

    /**
     * Create a new OpenEvent instance.
     *
     * @param  \Swoole\WebSocket\Server  $server
     * @param  \Swoole\Http\Request  $request
     * @return void
     */
    public function __construct($server, $request)
    {
        $this->server = $server;
        $this->request = $request;
    }
}
