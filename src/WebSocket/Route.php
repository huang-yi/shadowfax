<?php

namespace HuangYi\Swoole\WebSocket;

use Closure;
use HuangYi\Swoole\Exceptions\EventNotFoundException;
use HuangYi\Swoole\Exceptions\WebSocketException;
use Illuminate\Routing\Route as HttpRoute;
use Illuminate\Support\Str;

class Route extends HttpRoute
{
    /**
     * Events.
     *
     * @var array
     */
    protected $events = [];

    /**
     * Set connected callback.
     *
     * @param mixed $action
     * @return $this
     */
    public function connected($action)
    {
        return $this->uses($action);
    }

    /**
     * Register event.
     *
     * @param string $event
     * @param mixed $callback
     * @return $this
     * @throws \HuangYi\Swoole\Exceptions\WebSocketException
     */
    public function on($event, $callback)
    {
        $this->events[$event] = new Event(
            $event, $this->parseEventCallback($callback)
        );

        return $this;
    }

    /**
     * Get event.
     *
     * @param string $name
     * @return \HuangYi\Swoole\WebSocket\Event|null
     * @throws \HuangYi\Swoole\Exceptions\EventNotFoundException
     */
    public function getEvent($name)
    {
        if (! isset($this->events[$name])) {
            throw new EventNotFoundException("Event [$name] not found in route [{$this->uri()}].");
        }

        return $this->events[$name];
    }

    /**
     * Get events.
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Parse event callback.
     *
     * @param mixed $callback
     * @return array
     * @throws \HuangYi\Swoole\Exceptions\WebSocketException
     */
    protected function parseEventCallback($callback)
    {
        if ($callback instanceof Closure) {
            return $callback;
        }

        list($controller, $method) = Str::parseCallback($callback, '__invoke');

        if (! class_exists($controller)) {
            throw new WebSocketException("Class [$controller] doesn't exist.");
        }

        $controller = $this->container->make($controller);

        if (! method_exists($controller, $method)) {
            throw new WebSocketException("Class [$controller] has no method [$method].");
        }

        return [$controller, $method];
    }

    /**
     * Get the compiled version of the route.
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    public function getCompiled()
    {
        $this->compileRoute();

        return $this->compiled;
    }
}
