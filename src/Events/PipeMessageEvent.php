<?php

namespace HuangYi\Shadowfax\Events;

class PipeMessageEvent
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
     * The message.
     *
     * @var mixed
     */
    public $message;

    /**
     * Create a new PipeMessageEvent instance.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $wokerId
     * @param  mixed  $message
     * @return void
     */
    public function __construct($server, $wokerId, $message)
    {
        $this->server = $server;
        $this->workerId = $wokerId;
        $this->message = $message;
    }
}
