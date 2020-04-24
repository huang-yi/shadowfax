<?php

namespace HuangYi\Shadowfax\Events;

class ReceiveEvent
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
     * The received data.
     *
     * @var string
     */
    public $data;

    /**
     * Create a new ReceiveEvent instance.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $fd
     * @param  int  $reactorId
     * @param  string  $data
     * @return void
     */
    public function __construct($server, $fd, $reactorId, $data)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
        $this->data = $data;
    }
}
