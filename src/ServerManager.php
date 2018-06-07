<?php

namespace HuangYi\Swoole;

use HuangYi\Swoole\Contracts\TaskContract;
use HuangYi\Swoole\Transformers\RequestTransformer;
use HuangYi\Swoole\Transformers\ResponseTransformer;
use HuangYi\Swoole\Websocket\Message\Kernel as MessageKernel;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Http\Kernel as LaravelHttpKernel;
use Laravel\Lumen\Application as LumenApplication;
use RuntimeException;
use Swoole\Http\Server as HttpServer;
use Swoole\Table;
use Swoole\Websocket\Server as WebsocketServer;

class ServerManager
{
    /**
     * The illuminate container.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The Swoole Server.
     *
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * The laravel http kernel.
     *
     * @var \Illuminate\Contracts\Http\Kernel
     */
    protected $laravelHttpKernel;

    /**
     * The laravel websocket kernel.
     *
     * @var \Illuminate\Contracts\Http\Kernel
     */
    protected $laravelWebsocketKernel;

    /**
     * Server events.
     *
     * @var array
     */
    protected $events = [
        'start', 'shutDown', 'workerStart', 'workerStop', 'packet', 'close',
        'bufferFull', 'bufferEmpty', 'task', 'finish', 'pipeMessage',
        'workerError', 'managerStart', 'managerStop', 'request',
    ];

    /**
     * The swoole server Manager.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->init();
    }

    /**
     * Initialize.
     *
     * @return void
     * @throws \RuntimeException
     */
    protected function init()
    {
        $host = $this->getConfig('server.host', '127.0.0.1');
        $port = $this->getConfig('server.port', '1215');
        $options = $this->getConfig('server.options', []);
        $tables = $this->getConfig('tables', []);

        $this->server = $this->createServer($host, $port, $options);
        $this->server->tables = $this->createTables($tables);
    }

    /**
     * Start swoole server.
     *
     * @return void
     */
    public function start()
    {
        $this->getServer()->start();
    }

    /**
     * Stop swoole server.
     *
     * @return void
     */
    public function stop()
    {
        $this->getServer()->shutdown();
    }

    /**
     * Reload swoole server.
     *
     * @return void
     */
    public function reload()
    {
        $this->getServer()->reload();
    }

    /**
     * Create swoole server.
     *
     * @param string $host
     * @param string $port
     * @param array $options
     * @return \Swoole\Server
     * @throws \RuntimeException
     */
    public function createServer($host, $port, $options)
    {
        if ($this->enableWebsocket()) {
            if ($this->runInLumen()) {
                throw new RuntimeException('Websocket is not supported in Lumen.');
            }

            array_push($this->events, "open", "message");

            $server = new WebsocketServer($host, $port);
        } else {
            $server = new HttpServer($host, $port);
        }

        $server->set($options);

        foreach ($this->getEvents() as $event) {
            $this->registerEventListener($server, $event);
        }

        return $server;
    }

    /**
     * Create swoole tables.
     *
     * @param array $tables
     * @return \HuangYi\Swoole\TableManager
     */
    protected function createTables(array $tables)
    {
        return new TableManager($tables);
    }

    /**
     * Register the server's event listener.
     *
     * @param \Swoole\Http\Server $server
     * @param string $event
     * @return void
     */
    protected function registerEventListener($server, $event)
    {
        $listener = 'on' . ucfirst($event);

        if (method_exists($this, $listener)) {
            $server->on($event, [$this, $listener]);
        } else {
            $server->on($event, function () use ($event) {
                $event = sprintf('swoole.%s', $event);

                $this->container['events']->fire($event, func_get_args());
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
        $this->setProcessName('master process');
        $this->createPidFile();

        $this->container['events']->fire('swoole.start', func_get_args());
    }

    /**
     * The listener of "managerStart" event.
     *
     * @return void
     */
    public function onManagerStart()
    {
        $this->setProcessName('manager process');

        $this->container['events']->fire('swoole.managerStart', func_get_args());
    }

    /**
     * The listener of "workerStart" event.
     *
     * @return void
     */
    public function onWorkerStart()
    {
        $this->clearCache();
        $this->setProcessName('worker process');

        if (! $this->runInLumen()) {
            $this->laravelHttpKernel = $this->container->make(LaravelHttpKernel::class);
            $this->laravelHttpKernel->bootstrap();
        }

        if ($this->enableWebsocket()) {
            $this->laravelWebsocketKernel = $this->container['swoole.websocket.kernel'];
            $this->laravelWebsocketKernel->bootstrap();
        }

        $this->container->instance('swoole.server', $this);

        $this->container['events']->fire('swoole.workerStart', func_get_args());
    }

    /**
     * The listener of "request" event.
     *
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @return void
     */
    public function onRequest($request, $response)
    {
        $this->container->instance('swoole.http.request', $request);

        $illuminateRequest = RequestTransformer::make($request)->toIlluminateRequest();

        if ($this->runInLumen()) {
            $illuminateResponse = $this->container->handle($illuminateRequest);
        } else {
            $illuminateResponse = $this->laravelHttpKernel->handle($illuminateRequest);

            $this->laravelHttpKernel->terminate($illuminateRequest, $illuminateResponse);
        }

        ResponseTransformer::make($illuminateResponse)->send($response);
    }

    /**
     * The listener of "open" event.
     *
     * @param \Swoole\Websocket\Server $server
     * @param \Swoole\Http\Request $request
     * @return void
     */
    public function onOpen($server, $request)
    {
        $this->container->instance('swoole.http.request', $request);

        $illuminateRequest = RequestTransformer::make($request)->toIlluminateRequest();

        $illuminateResponse = $this->laravelWebsocketKernel->handle($illuminateRequest);

        $this->laravelHttpKernel->terminate($illuminateRequest, $illuminateResponse);
    }

    /**
     * The listener of "message" event.
     *
     * @param \Swoole\Websocket\Server $server
     * @param \Swoole\Websocket\Frame $frame
     * @return void
     */
    public function onMessage($server, $frame)
    {
        (new MessageKernel($this->container))->handle($frame);
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
        if ($task instanceof TaskContract) {
            $task->handle($server, $taskId, $srcWorkerId);
        }

        $this->container['events']->fire('swoole.task', func_get_args());
    }

    /**
     * The listener of "close" event.
     *
     * @param \Swoole\Server $server
     * @param int $fd
     * @param int $reactorId
     * @return void
     */
    public function onClose($server, $fd, $reactorId)
    {
        if ($this->isWebsocket($fd)) {
            $this->container['swoole.websocket.namespace']->leave($fd);
        }

        $this->container['events']->fire('swoole.close', func_get_args());
    }

    /**
     * The listener of "shutdown" event.
     *
     * @return void
     */
    public function onShutdown()
    {
        $this->removePidFile();

        $this->container['events']->fire('swoole.showdown', func_get_args());
    }

    /**
     * Get config.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig($key, $default = null)
    {
        return array_get($this->container['config']['swoole'], $key, $default);
    }

    /**
     * Determine if enable websocket.
     *
     * @return bool
     */
    public function enableWebsocket()
    {
        return $this->getConfig('websocket.enable', false);
    }

    /**
     * Determine if run in websocket.
     *
     * @param int $fd
     * @return bool
     */
    protected function isWebsocket($fd)
    {
        $client = $this->server->getClientInfo($fd);

        return array_key_exists('websocket_status', $client);
    }

    /**
     * Get server events.
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Get swoole websocket server.
     *
     * @return \Swoole\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Gets pid file path.
     *
     * @return string
     */
    protected function getPidFile()
    {
        return $this->getConfig('swoole.server.options.pid_file');
    }

    /**
     * Create pid file.
     *
     * @return void
     */
    protected function createPidFile()
    {
        $pidFile = $this->getPidFile();
        $pid = $this->getServer()->master_pid;

        file_put_contents($pidFile, $pid);
    }

    /**
     * Remove pid file.
     *
     * @return void
     */
    protected function removePidFile()
    {
        unlink($this->getPidFile());
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
     * Sets process name.
     *
     * @param string $process
     * @return void
     */
    protected function setProcessName($process)
    {
        // Mac OS doesn't support this function
        if ($this->isMacOS()) {
            return;
        }

        $serverName = 'swoole-http-server';
        $appName = $this->container['config']->get('app.name', 'Laravel');
        $host = $this->getConfig('server.host');
        $port = $this->getConfig('server.port');

        $name = sprintf('%s: %s for %s, %s:%s', $serverName, $process, $appName, $host, $port);

        swoole_set_process_name($name);
    }

    /**
     * @return bool
     */
    protected function isMacOS()
    {
        return PHP_OS == 'Darwin';
    }

    /**
     * Determine if run in the lumen framework.
     *
     * @return bool
     */
    protected function runInLumen()
    {
        return $this->container instanceof LumenApplication;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getServer()->$name;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getServer(), $name], $arguments);
    }
}
