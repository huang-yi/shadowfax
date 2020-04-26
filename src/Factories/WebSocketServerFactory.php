<?php

namespace HuangYi\Shadowfax\Factories;

use Swoole\WebSocket\Server;

class WebSocketServerFactory extends HttpServerFactory
{
    /**
     * The server events.
     *
     * @var array
     */
    protected $events = [
        'Close', 'Handshake', 'ManagerStart', 'ManagerStop', 'Message', 'Open',
        'PipMessage', 'Request', 'Shutdown', 'Start', 'Task', 'WorkerStart',
        'WorkerStop',
    ];

    /**
     * Define the server class.
     *
     * @return string
     */
    public function server(): string
    {
        return Server::class;
    }
}
