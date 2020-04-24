<?php

namespace HuangYi\Shadowfax\Factories;

use Swoole\Http\Server;

class HttpServerFactory extends ServerFactory
{
    /**
     * The server events.
     *
     * @var array
     */
    protected $events = [
        'ManagerStart', 'ManagerStop', 'PipMessage', 'Request', 'Shutdown',
        'Start', 'Task', 'WorkerStart', 'WorkerStop',
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
