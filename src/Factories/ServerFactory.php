<?php

namespace HuangYi\Shadowfax\Factories;

use HuangYi\Shadowfax\Contracts\EventDispatcher;
use HuangYi\Shadowfax\Contracts\ServerFactory as ServerFactoryContract;

abstract class ServerFactory implements ServerFactoryContract
{
    /**
     * The EventDispatcher instance.
     *
     * @var \HuangYi\Shadowfax\Contracts\EventDispatcher
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
     * The server socket type.
     *
     * @var int
     */
    protected $socket = SWOOLE_SOCK_TCP;

    /**
     * The server settings.
     *
     * @var array
     */
    protected $settings = [];

    /**
     * The server events.
     *
     * @var array
     */
    protected $events = [
        'ManagerStart', 'ManagerStop', 'PipMessage', 'Shutdown', 'Start',
        'Task', 'WorkerStart', 'WorkerStop',
    ];

    /**
     * Create a new HttpServerFactory instance.
     *
     * @param  \HuangYi\Shadowfax\Contracts\EventDispatcher  $dispatcher
     * @return void
     */
    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Define the server class.
     *
     * @return string
     */
    abstract public function server(): string;

    /**
     * Create the Swoole HTTP server instance.
     *
     * @return \Swoole\Http\Server
     */
    public function create()
    {
        $class = $this->server();

        $server = new $class($this->host, $this->port, $this->mode, $this->socket);

        $server->set($this->settings);

        foreach ($this->events as $event) {
            if (strcasecmp($event, 'start') === 0 && $server->mode == SWOOLE_BASE) {
                continue;
            }

            $this->delegateEvent($server, $event);
        }

        return $server;
    }

    /**
     * Delegate the event.
     *
     * @param  \Swoole\Server  $server
     * @param  string  $event
     * @return void
     */
    protected function delegateEvent($server, $event)
    {
        $class = "\\HuangYi\\Shadowfax\\Events\\".ucfirst($event)."Event";

        if (class_exists($class)) {
            $server->on($event, function (...$args) use ($class) {
                $this->dispatcher->dispatch(new $class(...$args));
            });
        }
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
     * Set the server socket type.
     *
     * @param  int  $socket
     * @return $this
     */
    public function setSocket(int $socket)
    {
        $this->socket = $socket;

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

    /**
     * Set the server events.
     *
     * @param  array  $events
     * @return $this
     */
    public function setEvents(array $events)
    {
        $events = array_unique(array_filter($events));

        if ($events) {
            $this->events = $events;
        }

        return $this;
    }
}
