<?php

namespace HuangYi\Shadowfax\Events;

class MessageEvent
{
    /**
     * The WebSocket server instance.
     *
     * @var \Swoole\WebSocket\Server
     */
    public $server;

    /**
     * The WebSocket frame instance.
     *
     * @var \Swoole\WebSocket\Frame
     */
    public $frame;

    /**
     * Create a new MessageEvent instance.
     *
     * @param  \Swoole\WebSocket\Server  $server
     * @param  \Swoole\WebSocket\Frame  $frame
     * @return void
     */
    public function __construct($server, $frame)
    {
        $this->server = $server;
        $this->frame = $frame;
    }
}
