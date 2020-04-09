<?php

namespace HuangYi\Shadowfax\Factories;

use HuangYi\Shadowfax\Contracts\ServerFactory;
use Swoole\Http\Server;
use Symfony\Component\EventDispatcher\EventDispatcher;

class HttpServerFactory implements ServerFactory
{
    /**
     * The EventDispatcher instance.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * The server host.
     *
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * The server port.
     *
     * @var int
     */
    protected $port = 1215;

    /**
     * The server mode.
     *
     * @var int
     */
    protected $mode = SWOOLE_PROCESS;

    /**
     * The server settings.
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Create a new HttpServerFactory instance.
     *
     * @param  \Symfony\Component\EventDispatcher\EventDispatcher  $dispatcher
     * @return void
     */
    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Create the Swoole HTTP server instance.
     *
     * @return \Swoole\Http\Server
     */
    public function create()
    {
        $class = $this->server();

        $server = new $class($this->host, $this->port, $this->mode);

        $server->set($this->settings);

        foreach ($this->events() as $event => $delegate) {
            if ($event == 'start' && $server->mode == SWOOLE_BASE) {
                continue;
            }

            $server->on($event, function (...$args) use ($delegate) {
                $this->dispatcher->dispatch(new $delegate(...$args));
            });
        }

        return $server;
    }

    /**
     * Define the server class.
     *
     * @return string
     */
    public function server(): string
    {
        return Server::class;
    }

    /**
     * Get the events list.
     *
     * @return array
     */
    public function events(): array
    {
        return [
            'managerStart' => \HuangYi\Shadowfax\Events\ManagerStartEvent::class,
            'managerStop' => \HuangYi\Shadowfax\Events\ManagerStopEvent::class,
            'request' => \HuangYi\Shadowfax\Events\RequestEvent::class,
            'shutdown' => \HuangYi\Shadowfax\Events\ShutdownEvent::class,
            'start' => \HuangYi\Shadowfax\Events\StartEvent::class,
            'task' => \HuangYi\Shadowfax\Events\TaskEvent::class,
            'workerStart' => \HuangYi\Shadowfax\Events\WorkerStartEvent::class,
            'workerStop' => \HuangYi\Shadowfax\Events\WorkerStopEvent::class,
        ];
    }

    /**
     * Set the server host.
     *
     * @param  string  $host
     * @return $this
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Set the server port.
     *
     * @param  int  $port
     * @return $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Set the server mode.
     *
     * @param  int  $mode
     * @return $this
     */
    public function setMode(int $mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Set the server settings.
     *
     * @param  array  $settings
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }
}
