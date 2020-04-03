<?php

namespace HuangYi\Shadowfax\WebSocket;

use HuangYi\Shadowfax\Contracts\WebSocket\Handler;
use HuangYi\Shadowfax\Contracts\WebSocket\Router;
use HuangYi\Shadowfax\Http\Request;
use Laravel\Lumen\Routing\Router as BaseRouter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LumenRouter extends BaseRouter implements Router
{
    /**
     * Listen a uri.
     *
     * @param  string  $uri
     * @param  Handler  $handler
     * @return LumenRouter
     */
    public function listen(string $uri, Handler $handler)
    {
        return $this->get($uri, ['handler' => $handler]);
    }

    /**
     * Find the handler.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @return \HuangYi\Shadowfax\Contracts\WebSocket\Handler
     */
    public function findHandler(Request $request): Handler
    {
        $method = $request->getIlluminateRequest()->getMethod();
        $pathInfo = '/'.trim($request->getIlluminateRequest()->getPathInfo(), '/');

        if (! $route = $this->getRoutes()[$method.$pathInfo] ?? null) {
            throw new NotFoundHttpException();
        }

        $handler = $route['action']['handler'] ?? null;

        if (! $handler instanceof Handler) {
            throw new NotFoundHttpException();
        }

        return $handler;
    }
}
