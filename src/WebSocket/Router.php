<?php

namespace HuangYi\Swoole\WebSocket;

use Illuminate\Routing\Router as HttpRouter;

class Router extends HttpRouter
{
    /**
     * Define a kind of room.
     *
     * @param string $uri
     * @param mixed $action
     * @return \HuangYi\Swoole\WebSocket\Route
     */
    public function room($uri, $action)
    {
        return $this->get($uri, $action);
    }

    /**
     * Create a new Route object.
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  mixed  $action
     * @return \Illuminate\Routing\Route
     */
    protected function newRoute($methods, $uri, $action)
    {
        return (new Route($methods, $uri, $action))
            ->setRouter($this)
            ->setContainer($this->container);
    }
}
