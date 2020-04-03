<?php

namespace HuangYi\Shadowfax\Events;

class CloseEvent
{
    /**
     * The Swoole server instance.
     *
     * @var \Swoole\Server
     */
    public $server;

    /**
     * The connection's file descriptor.
     *
     * @var int
     */
    public $fd;

    /**
     * The reactor id.
     *
     * @var int
     */
    public $reactorId;

    /**
     * Create a new CloseEvent instance.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $fd
     * @param  int  $reactorId
     * @return void
     */
    public function __construct($server, $fd, $reactorId)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }
}
