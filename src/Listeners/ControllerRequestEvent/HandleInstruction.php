<?php

namespace HuangYi\Shadowfax\Listeners\ControllerRequestEvent;

use HuangYi\Shadowfax\Events\ControllerRequestEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;
use Throwable;

class HandleInstruction
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\ControllerRequestEvent  $event
     * @return void
     */
    public function handle(ControllerRequestEvent $event)
    {
        try {
            $instruction = trim($event->request->server['request_uri'], '/');

            switch ($instruction) {
                case 'stop':
                    $this->stop($event->request, $event->response);

                    break;

                case 'reload':
                    $this->reload($event->request, $event->response);

                    break;

                case 'reload-task':
                    $this->reloadTask($event->request, $event->response);

                    break;

                default:
                    $this->undefinedInstruction($instruction, $event->request, $event->response);

                    break;
            }
        } catch (Throwable $e) {
            $this->serverError($e, $event->request, $event->response);
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
        $this->output('<info>Stopping the Shadowfax server...<info>');

        $this->server()->shutdown();

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
        if ($this->isSingleProcess($this->server())) {
            $response->status(403);
            $response->end('Cannot reload a single process server.');

            return;
        }

        $this->output('<info>Reloading all worker processes...</info>');

        $this->server()->reload();

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
        if ($this->isSingleProcess($this->server())) {
            $response->status(403);
            $response->end('Cannot reload a single process server.');

            return;
        }

        $this->server()->reload(true);

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
        $this->output("<error>Received a valid instruction [$instruction]</error>");

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
        $this->output("<error>An error occurred on the controller server: {$exception->getMessage()}</error>");

        $response->status(500);
        $response->end("Controller port error [{$exception->getMessage()}].");
    }

    /**
     * Get the Swoole server instance.
     *
     * @return \Swoole\Server
     */
    protected function server()
    {
        return shadowfax('server');
    }
}
