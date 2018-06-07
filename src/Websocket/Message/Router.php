<?php

namespace HuangYi\Swoole\Websocket\Message;

use Closure;
use HuangYi\Swoole\Contracts\MessageContract;
use Illuminate\Contracts\Container\Container;

class Router
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Route collection.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $routes;

    /**
     * Route middleware.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Router.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->routes = collect();
    }

    /**
     * Bind websocket events.
     *
     * @param string $event
     * @param mixed $action
     * @return void
     */
    public function on($event, $action)
    {
        if ($this->actionReferencesController($action)) {
            $action = $this->convertToControllerAction($action);
        }

        if ($action instanceof Closure) {
            $action = ['uses' => $action];
        }

        $route = $this->createRoute($event, $action);

        $this->routes->put($event, $route);
    }

    /**
     * Create a route.
     *
     * @param string $event
     * @param array $action
     * @return \HuangYi\Swoole\Websocket\Message\Route
     */
    protected function createRoute($event, $action)
    {
        return (new Route($event, $action))
            ->setRouter($this)
            ->setContainer($this->container);
    }

    /**
     * Determine if the action is routing to a controller.
     *
     * @param  array $action
     * @return bool
     */
    protected function actionReferencesController($action)
    {
        if (!$action instanceof Closure) {
            return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
        }

        return false;
    }

    /**
     * Add a controller based route action to the action array.
     *
     * @param  array|string $action
     * @return array
     */
    protected function convertToControllerAction($action)
    {
        if (is_string($action)) {
            $action = ['uses' => $action];
        }

        return $action;
    }

    /**
     * Find route.
     *
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @return mixed|null
     */
    public function findRoute(MessageContract $message)
    {
        $event = $message->getEvent();

        return $this->routes[$event] ?? null;
    }

    /**
     * Set middleware alias.
     *
     * @param string|array $alias
     * @param mixed $middleware
     * @return void
     */
    public function aliasMiddleware($alias, $middleware = null)
    {
        if (is_array($alias)) {
            $this->middleware = array_merge($this->middleware, $alias);
        } else {
            $this->middleware[$alias] = $middleware;
        }
    }

    /**
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
}
