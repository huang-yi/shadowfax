<?php

namespace HuangYi\Swoole\Http;

use HuangYi\Swoole\Foundation\Contracts\ApplicationContract;
use HuangYi\Swoole\Foundation\Contracts\ServerContract;
use HuangYi\Swoole\Foundation\LaravelApplication;
use HuangYi\Swoole\Foundation\LumenApplication;
use HuangYi\Swoole\Http\Request as HttpRequest;
use HuangYi\Swoole\Http\Response as HttpResponse;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request as IlluminateRequest;
use Swoole\Http\Server;

class ServerManager implements ServerContract
{
    /**
     * The Laravel/Lumen container.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The Swoole Http Server.
     *
     * @var \Swoole\Http\Server
     */
    protected $server;

    /**
     * @var \HuangYi\Swoole\Foundation\Contracts\ApplicationContract
     */
    protected $application;

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
     * The swoole-http-server Manager.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->server = $this->createServer();

        $this->macros();
    }

    /**
     * Start swoole-http-server.
     *
     * @return void
     */
    public function start()
    {
        $this->getServer()->start();
    }

    /**
     * Stop swoole-http-server.
     *
     * @return void
     */
    public function stop()
    {
        $this->getServer()->shutdown();
    }

    /**
     * Reload swoole-http-server.
     *
     * @return void
     */
    public function reload()
    {
        $this->getServer()->reload();
    }

    /**
     * Create swoole-http-server.
     *
     * @return \Swoole\Http\Server
     */
    protected function createServer()
    {
        $host = $this->getConfig('host');
        $port = $this->getConfig('port');
        $options = $this->getConfig('options');

        $server = new Server($host, $port);

        $server->set($options);

        foreach ($this->getEvents() as $event) {
            $this->registerEventListener($server, $event);
        }

        return $server;
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
                $event = sprintf('http.%s', $event);

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

        $this->container['events']->fire('http.start', func_get_args());
    }

    /**
     * The listener of "managerStart" event.
     *
     * @return void
     */
    public function onManagerStart()
    {
        $this->setProcessName('manager process');

        $this->container['events']->fire('http.managerStart', func_get_args());
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

        $this->container['events']->fire('http.workerStart', func_get_args());

        $this->bootstrapApplication();
    }

    /**
     * The listener of "request" event.
     *
     * @param \Swoole\Http\Request $swooleRequest
     * @param \Swoole\Http\Response $swooleResponse
     * @return void
     */
    public function onRequest($swooleRequest, $swooleResponse)
    {
        $illuminateRequest = HttpRequest::make($swooleRequest)->toIlluminate();
        $illuminateResponse = $this->getApplication()->run($illuminateRequest);

        HttpResponse::make($illuminateResponse, $swooleResponse)->send();
    }

    /**
     * The listener of "shutdown" event.
     *
     * @return void
     */
    public function onShutdown()
    {
        $this->removePidFile();

        $this->container['events']->fire('http.showdown', func_get_args());
    }

    /**
     * Bootstrap application.
     *
     * @return void
     */
    protected function bootstrapApplication()
    {
        if ($this->isLumen()) {
            $this->application = new LumenApplication($this->container);
        } else {
            $this->application = new LaravelApplication($this->container);
        }
    }

    /**
     * Get application.
     *
     * @return \HuangYi\Swoole\Foundation\Contracts\ApplicationContract
     */
    public function getApplication()
    {
        if (! $this->application instanceof ApplicationContract) {
            $this->bootstrapApplication();
        }

        return $this->application;
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
        return array_get($this->container['config']['http'], $key, $default);
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
     * @return \Swoole\Http\Server
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
        return $this->getConfig('options.pid_file');
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
        $host = $this->getConfig('host');
        $port = $this->getConfig('port');

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
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->container->version(), 'Lumen');
    }

    /**
     * Request macros.
     *
     * @return void
     */
    protected function macros()
    {
        IlluminateRequest::macro('setSwooleServer', function ($server) {
            $this->swooleServer = $server;

            return $this;
        });

        IlluminateRequest::macro('getSwooleServer', function () {
            return $this->swooleServer;
        });
    }
}
