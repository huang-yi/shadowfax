<?php

namespace HuangYi\Shadowfax\WebSocket;

use HuangYi\Shadowfax\Contracts\WebSocket\Handler;
use HuangYi\Shadowfax\Contracts\WebSocket\Router;
use HuangYi\Shadowfax\Laravel\InjectableMethodToClosure;
use Laravel\Lumen\Routing\Router as BaseRouter;

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
        if (method_exists($handler, 'onHandshake')) {
            $uses = InjectableMethodToClosure::transform($handler, 'onHandshake');
        } else {
            $uses = function () {
                //
            };
        }

        return $this->get($uri, ['handler' => $handler, $uses]);
    }
}
