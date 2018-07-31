<?php

namespace HuangYi\Swoole;

use HuangYi\Swoole\Contracts\TaskContract;
use Illuminate\Contracts\Container\Container;

abstract class Server
{
    /**
     * Server host.
     *
     * @var string
     */
    protected $host;

    /**
     * Server port.
     *
     * @var int
     */
    protected $port;

    /**
     * Swoole server.
     *
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * Illuminate container.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Server events.
     *
     * @var array
     */
    protected $events = [];

    /**
     * Base events.
     *
     * @var array
     */
    protected $baseEvents = [
        'start', 'shutdown', 'workerStart', 'workerStop', 'packet', 'close',
        'bufferFull', 'bufferEmpty', 'task', 'finish', 'pipeMessage',
        'workerError', 'managerStart', 'managerStop',
    ];

    /**
     * Define swoole server class.
     *
     * @return string
     */
    abstract public function swooleServer();

    /**
     * Http Server.
     *
     * @param string $host
     * @param int $port
     * @param array $options
     */
    public function __construct($host, $port, array $options)
    {
        $this->host = $host;
        $this->port = $port;
        $this->server = $this->createSwooleServer($host, $port, $options);
    }

    /**
     * Create swoole server.
     *
     * @param string $host
     * @param int $port
     * @param array $options
     * @return \Swoole\Server
     */
    protected function createSwooleServer($host, $port, array $options)
    {
        $class = $this->swooleServer();

        $server = new $class($host, $port);

        $server->set($options);

        foreach ($this->getEvents() as $event) {
            $this->registerEvent($server, $event);
        }

        return $server;
    }

    /**
     * Register swoole server event.
     *
     * @param \Swoole\Server $server
     * @param string $event
     * @return void
     */
    protected function registerEvent($server, $event)
    {
        $listener = 'on' . ucfirst($event);

        if (method_exists($this, $listener)) {
            $server->on($event, [$this, $listener]);
        } else {
            $server->on($event, function (...$arguments) use ($event) {
                $event = sprintf('swoole.%s', $event);

                $this->container['events']->fire($event, $arguments);
            });
        }
    }

    /**
     * The listener of "start" event.
     *
     * @return void
     */
    public function onStart()
    {
        $this->container['events']->fire('swoole.start', func_get_args());

        $this->setProcessName('master process');
    }

    /**
     * The listener of "managerStart" event.
     *
     * @return void
     */
    public function onManagerStart()
    {
        $this->container['events']->fire('swoole.managerStart', func_get_args());

        $this->setProcessName('manager process');
    }

    /**
     * The listener of "workerStart" event.
     *
     * @param \Swoole\Server $server
     * @param int $workerId
     * @return void
     */
    public function onWorkerStart($server, $workerId)
    {
        $this->container['events']->fire('swoole.workerStart', func_get_args());

        if ($this->isTaskWorker($workerId)) {
            $this->setProcessName('task process');
        } else {
            $this->setProcessName('worker process');
        }
    }

    /**
     * The listener of "task" event.
     *
     * @param \Swoole\Server $server
     * @param int $taskId
     * @param int $srcWorkerId
     * @param mixed $task
     * @return void
     */
    public function onTask($server, $taskId, $srcWorkerId, $task)
    {
        $this->container['events']->fire('swoole.task', func_get_args());

        if ($task instanceof TaskContract) {
            $task->handle($server, $taskId, $srcWorkerId);
        }
    }

    /**
     * Sets process name.
     *
     * @param string $process
     * @return void
     */
    protected function setProcessName($process)
    {
        // MacOS doesn't support modifying process name.
        if ($this->isMacOS()) {
            return;
        }

        swoole_set_process_name(sprintf(
            '%s: %s[%s:%s]',
            $this->getServerName(),
            $process,
            $this->host,
            $this->port
        ));
    }

    /**
     * Determine whether the process is task process.
     *
     * @param int $workerId
     * @return bool
     */
    protected function isTaskWorker($workerId)
    {
        return $workerId >= $this->server->setting['worker_num'];
    }

    /**
     * Determine whether the process is running in macOS.
     *
     * @return bool
     */
    protected function isMacOS()
    {
        return PHP_OS == 'Darwin';
    }

    /**
     * Get server name
     *
     * @return string
     */
    protected function getServerName()
    {
        return 'swoole-server';
    }

    /**
     * Clear APC or OPCache.
     *
     * @return void
     */
    protected function clearCache()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    /**
     * Set illuminate container.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get server events.
     *
     * @return array
     */
    public function getEvents()
    {
        return array_unique(
            array_merge($this->baseEvents, $this->events)
        );
    }

    /**
     * Getter.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->server->$key;
    }

    /**
     * Caller.
     *
     * @param string $function
     * @param array $arguments
     * @return mixed
     */
    public function __call($function, $arguments)
    {
        return $this->server->$function(...$arguments);
    }
}
