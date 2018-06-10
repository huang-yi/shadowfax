<?php

namespace HuangYi\Swoole\Websocket\Message;

use HuangYi\Swoole\Contracts\MessageContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\MiddlewareNameResolver;
use Illuminate\Support\Str;

class Route
{
    /**
     * The event name.
     *
     * @var string
     */
    protected $event;

    /**
     * The action array.
     *
     * @var array
     */
    protected $action;

    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * @var \HuangYi\Swoole\Websocket\Message\Router
     */
    protected $router;

    /**
     * Route.
     *
     * @param string $event
     * @param array $action
     */
    public function __construct($event, array $action)
    {
        $this->event = $event;
        $this->action = $action;
    }

    /**
     * Run the route action.
     *
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @return mixed
     */
    public function run(MessageContract $message)
    {
        if ($this->isControllerAction()) {
            return $this->runController($message);
        }

        return $this->runCallable($message);
    }

    /**
     * Checks whether the route's action is a controller.
     *
     * @return bool
     */
    protected function isControllerAction()
    {
        return is_string($this->action['uses']);
    }

    /**
     * Run the callable route action.
     *
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @return mixed
     */
    protected function runCallable(MessageContract $message)
    {
        $callable = $this->action['uses'];

        return $callable($message);
    }

    /**
     * Run the controller.
     *
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @return mixed
     */
    protected function runController(MessageContract $message)
    {
        list($class, $method) = Str::parseCallback($this->action['uses'], 'handle');

        $controller = $this->container->make($class);

        return call_user_func([$controller, $method], $message);
    }

    /**
     * Get all middleware.
     *
     * @return array
     */
    public function gatherMiddleware()
    {
        $middleware = (array) (array_get($this->action, 'middleware', []));

        if (! empty($middleware)) {
            $middleware = collect($middleware)->map(function ($name) {
                return (array) MiddlewareNameResolver::resolve($name, $this->router->getMiddleware(), []);
            })->flatten()->toArray();
        }

        return $middleware;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param \Illuminate\Contracts\Container\Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @param \HuangYi\Swoole\Websocket\Message\Router $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }
}
