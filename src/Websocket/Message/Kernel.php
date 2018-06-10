<?php

namespace HuangYi\Swoole\Websocket\Message;

use Exception;
use HuangYi\Swoole\Contracts\MessageContract;
use HuangYi\Swoole\Contracts\ParserContract;
use HuangYi\Swoole\Exceptions\EventNotFoundException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class Kernel
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Websocket Application.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Handle websocket message.
     *
     * @param \Swoole\Websocket\Frame $frame
     * @return void
     */
    public function handle($frame)
    {
        try {
            $message = $this->parse($frame->data);
            $message->setSocketId($frame->fd);

            $route = $this->findRoute($message);

            $this->dispatchRoute($message, $route);
        } catch (Exception $e) {
            $this->container[ExceptionHandler::class]->report($e);
        } catch (Throwable $e) {
            $this->container[ExceptionHandler::class]->report(new FatalThrowableError($e));
        }
    }

    /**
     * Parse.
     *
     * @param string $payload
     * @return \HuangYi\Swoole\Contracts\MessageContract
     */
    protected function parse($payload)
    {
        $parser = $this->getParser();

        return $this->payloadToMessage($parser, $payload);
    }

    /**
     * Get parser.
     *
     * @return \HuangYi\Swoole\Contracts\ParserContract
     */
    protected function getParser()
    {
        $parserClass = $this->container['config']->get('swoole.message_parser', JsonParser::class);

        return $this->container->make($parserClass);
    }

    /**
     * Parse payload to message.
     *
     * @param \HuangYi\Swoole\Contracts\ParserContract $parser
     * @param string $payload
     * @return \HuangYi\Swoole\Contracts\MessageContract
     */
    protected function payloadToMessage(ParserContract $parser, $payload)
    {
        return $parser->parse($payload);
    }

    /**
     * Find route.
     *
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @return \HuangYi\Swoole\Websocket\Message\Route
     * @throws \HuangYi\Swoole\Exceptions\EventNotFoundException
     */
    protected function findRoute(MessageContract $message)
    {
        $route = $this->container['swoole.websocket.message.router']->findRoute($message);

        if (is_null($route)) {
            throw new EventNotFoundException;
        }

        return $route;
    }

    /**
     * Dispatch route.
     *
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @param \HuangYi\Swoole\Websocket\Message\Route $route
     * @return void
     */
    protected function dispatchRoute(MessageContract $message, Route $route)
    {
        (new Pipeline($this->container))
            ->send($message)
            ->through($route->gatherMiddleware())
            ->then(function ($message) use ($route) {
                $route->run($message);
            });
    }
}
