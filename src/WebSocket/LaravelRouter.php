<?php

namespace HuangYi\Shadowfax\WebSocket;

use HuangYi\Shadowfax\Contracts\WebSocket\Handler;
use HuangYi\Shadowfax\Contracts\WebSocket\Router;
use HuangYi\Shadowfax\Http\Request;
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
            'uses' => function () {
                //
            },
        ]);
    }

    /**
     * Find the handler for a given request.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @return \HuangYi\Shadowfax\Contracts\WebSocket\Handler
     */
    public function findHandler(Request $request): Handler
    {
        $handler = $this->routes->match(
            $request->getIlluminateRequest()
        )->getAction('handler');

        if (! $handler instanceof Handler) {
            throw new NotFoundHttpException;
        }

        return $handler;
    }
}
