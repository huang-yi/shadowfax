<?php

namespace HuangYi\Shadowfax\Server;

use Exception;
use HuangYi\Shadowfax\Composer;
use HuangYi\Shadowfax\ContainerRewriter;
use Swoole\Http\Server;

class Starter extends Action
{
    /**
     * The swoole server events.
     *
     * @var array
     */
    protected $events = [
        'Start', 'ManagerStart', 'WorkerStart', 'Request', 'Task',
        'WorkerStop', 'ManagerStop', 'Shutdown',
    ];

    /**
     * Start the server.
     *
     * @return void
     */
    public function start()
    {
        $this->rewriteContainer();

        $server = $this->createServer();

        $this->output->writeln(sprintf(
            '<info>Starting the Shadowfax server: %s:%d</info>',
            $server->host,
            $server->port
        ));

        $this->createControllerServer($server);

        $this->unregisterAutoload();

        $server->start();
    }

    /**
     * Rewrite the illuminate container.
     *
     * @return void
     */
    protected function rewriteContainer()
    {
        $rewriter = new ContainerRewriter;

        $rewriter->rewrite();

        $this->shadowfax()->instance(ContainerRewriter::class, $rewriter);
    }

    /**
     * Create the server.
     *
     * @return \Swoole\Http\Server
     */
    protected function createServer()
    {
        $server = new Server(
            $this->getHost(),
            $this->getPort(),
            $this->getMode()
        );

        $server->set($this->config('server', []));

        foreach ($this->events as $name) {
            if ($name == 'Start' && $server->mode == SWOOLE_BASE) {
                continue;
            }

            $eventClass = "\\HuangYi\\Shadowfax\\Server\\Events\\{$name}Event";

            $server->on($name, [new $eventClass($this->output), 'handle']);
        }

        return $server;
    }

    /**
     * Create the controller server.
     *
     * @param  \Swoole\Http\Server  $server
     * @return void
     */
    protected function createControllerServer($server)
    {
        $ctl = $server->addListener(
            $this->getControllerHost(),
            $this->getControllerPort(),
            SWOOLE_SOCK_TCP
        );

        $ctl->on('Request', function ($request, $response) use ($server) {
            try {
                $instruction = trim($request->server['request_uri'], '/');

                switch ($instruction) {
                    case 'stop':
                        $server->shutdown();
                        $response->end();

                        break;

                    case 'reload':
                        if ($this->isSingleProcessServer($server)) {
                            $response->status(403);
                            $response->end('Cannot reload a single process server.');
                        } else {
                            $server->reload();
                            $response->end();
                        }

                        break;

                    case 'reload-task':
                        if ($this->isSingleProcessServer($server)) {
                            $response->status(403);
                            $response->end('Cannot reload a single process server.');
                        } else {
                            $server->reload(true);
                            $response->end();
                        }

                        break;

                    default:
                        $response->status(404);
                        $response->end("Undefined instruction [$instruction].");

                        break;
                }
            } catch (Exception $e) {
                $response->status(500);
                $response->end("Controller server error [{$e->getMessage()}].");
            }
        });
    }

    /**
     * Determine if the Shadowfax is a single process server.
     *
     * @param  \Swoole\Server  $server
     * @return bool
     */
    public function isSingleProcessServer($server)
    {
        if ($server->mode != SWOOLE_BASE) {
            return false;
        }

        return $server->setting['worker_num'] == 1;
    }

    /**
     * Unregister autoload.
     *
     * @return void
     */
    protected function unregisterAutoload()
    {
        $this->shadowfax()->make(Composer::class)->unregister();
    }

    /**
     * Get the server host.
     *
     * @return string
     */
    protected function getHost()
    {
        if ($host = $this->input->getOption('host')) {
            return $host;
        }

        return $this->config('host', '127.0.0.1');
    }

    /**
     * Get the server port.
     *
     * @return int
     */
    protected function getPort()
    {
        if ($port = $this->input->getOption('port')) {
            return (int) $port;
        }

        return $this->config('port', '1215');
    }

    /**
     * Get the server mode.
     *
     * @return int
     */
    protected function getMode()
    {
        return $this->config('mode', 'process') == 'base' ?
            SWOOLE_BASE : SWOOLE_PROCESS;
    }
}
