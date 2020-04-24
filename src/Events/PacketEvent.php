<?php

namespace HuangYi\Shadowfax\Events;

class PacketEvent
{
    /**
     * The Swoole server instance.
     *
     * @var \Swoole\Server
     */
    public $server;

    /**
     * The received data.
     *
     * @var string
     */
    public $data;

    /**
     * The client information.
     *
     * @var array
     */
    public $client;

    /**
     * Create a new PacketEvent instance.
     *
     * @param  \Swoole\Server  $server
     * @param  string  $data
     * @param  array  $client
     * @return void
     */
    public function __construct($server, $data, $client)
    {
        $this->server = $server;
        $this->data = $data;
        $this->client = $client;
    }
}
