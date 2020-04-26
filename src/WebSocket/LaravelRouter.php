<?php

namespace HuangYi\Shadowfax\WebSocket;

use HuangYi\Shadowfax\Contracts\WebSocket\Handler;
use HuangYi\Shadowfax\Contracts\WebSocket\Router;
use Illuminate\Routing\Router as BaseRouter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LaravelRouter extends BaseRouter implements Router
{
    /**
     * Create a WebSocket route.
     *
     * @param  string  $uri
     * @param  \HuangYi\Shadowfax\Contracts\WebSocket\Handler  $handler
     * @return \Illuminate\Routing\Route
     */
    public function listen(string $uri, Handler $handler)
    {
        return $this->get($uri, [
            'handler' => $handler,
            'middleware' => VerifiesRequest::class,
            'uses' => function () {
                //
            },
        ]);
    }

    /**
     * Create a new Route object.
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  mixed  $action
     * @return \Illuminate\Routing\Route
     */
    public function newRoute($methods, $uri, $action)
    {
        return (new LaravelRoute($methods, $uri, $action))
            ->setRouter($this)
            ->setContainer($this->container);
    }

    /**
     * Find the route matching a given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Routing\Route
     */
    public function findRoute($request)
    {
        $route = parent::findRoute($request);

        $handler = $route->getAction('handler');

        if (! $handler instanceof Handler) {
            throw new NotFoundHttpException;
        }

        return $route;
    }
}
