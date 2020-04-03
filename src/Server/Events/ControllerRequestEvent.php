<?php

namespace HuangYi\Shadowfax\Server\Events;

use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ControllerRequestEvent extends Event
{
    /**
     * The Swoole server.
     *
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * Event constructor.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \Swoole\Http\Server  $server
     * @return void
     */
    public function __construct(OutputInterface $output, $server)
    {
        $this->output = $output;
        $this->server = $server;
    }

    /**
     * Handle the event.
     *
     * @param  mixed  ...$args
     * @return void
     */
    public function handle(...$args)
    {
        try {
            $instruction = trim($args[0]->server['request_uri'], '/');

            switch ($instruction) {
                case 'stop':
                    $this->stop(...$args);

                    break;

                case 'reload':
                    $this->reload(...$args);

                    break;

                case 'reload-task':
                    $this->reloadTask(...$args);

                    break;

                default:
                    $this->undefinedInstruction($instruction, ...$args);

                    break;
            }
        } catch (Throwable $e) {
            $this->serverError($e, ...$args);
        }
    }

    /**
     * Stop the Shadowfax server.
     *
     * @param  \Swoole\Http\Request  $request
     * @param  \Swoole\Http\Response  $response
     * @return void
     */
    protected function stop($request, $response)
    {
        $this->output->writeln('<info>Stopping the Shadowfax server...<info>');

        $this->server->shutdown();
        $response->end();
    }

    /**
     * Reload the worker processes.
     *
     * @param  \Swoole\Http\Request  $request
     * @param  \Swoole\Http\Response  $response
     * @return void
     */
    protected function reload($request, $response)
    {
        if ($this->isSingleProcess($this->server)) {
            $response->status(403);
            $response->end('Cannot reload a single process server.');

            return;
        }

        $this->output->writeln('<info>Reloading all worker processes...</info>');

        $this->server->reload();
        $response->end();
    }

    /**
     * Reload the Shadowfax server.
     *
     * @param  \Swoole\Http\Request  $request
     * @param  \Swoole\Http\Response  $response
     * @return void
     */
    protected function reloadTask($request, $response)
    {
        if ($this->isSingleProcess($this->server)) {
            $response->status(403);
            $response->end('Cannot reload a single process server.');

            return;
        }

        $this->server->reload(true);
        $response->end();
    }

    /**
     * Undefined instruction.
     *
     * @param  string  $instruction
     * @param  \Swoole\Http\Request  $request
     * @param  \Swoole\Http\Response  $response
     * @return void
     */
    protected function undefinedInstruction($instruction, $request, $response)
    {
        $this->output->writeln("<error>Received a valid instruction [$instruction]</error>");

        $response->status(404);
        $response->end("Undefined instruction [$instruction].");
    }

    /**
     * Server error.
     *
     * @param  \Throwable  $exception
     * @param  \Swoole\Http\Request  $request
     * @param  \Swoole\Http\Response  $response
     * @return void
     */
    protected function serverError($exception, $request, $response)
    {
        $this->output->writeln("<error>An error occurred on the controller server: {$exception->getMessage()}</error>");

        $response->status(500);
        $response->end("Controller server error [{$exception->getMessage()}].");
    }
}
