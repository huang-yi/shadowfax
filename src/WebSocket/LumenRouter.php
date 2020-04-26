<?php

namespace HuangYi\Shadowfax\WebSocket;

use HuangYi\Shadowfax\Contracts\WebSocket\Handler;
use HuangYi\Shadowfax\Contracts\WebSocket\Router;
use Laravel\Lumen\Routing\Router as BaseRouter;
use ReflectionObject;

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
            $uses = $this->transOnHandshakeToClosure($handler);
        } else {
            $uses = function () {
                //
            };
        }

        return $this->get($uri, ['handler' => $handler, $uses]);
    }

    /**
     * Transform the 'onHandshake' method to a closure.
     *
     * @param  \HuangYi\Shadowfax\Contracts\WebSocket\Handler  $handler
     * @return \Closure
     */
    protected function transOnHandshakeToClosure(Handler $handler)
    {
        $object = new ReflectionObject($handler);
        $method = $object->getMethod('onHandshake');

        $params = [];

        foreach ($method->getParameters() as $param) {
            if (is_null($param->getClass())) {
                $params[] = '$'.$param->name;
            } elseif ($param->isVariadic()) {
                $params[] = $param->getClass()->name.' ...$'.$param->name;
            } else {
                $params[] = $param->getClass()->name.' $'.$param->name;
            }
        }

        $closure = '$closure = function ('.implode(', ', $params).') use ($handler) {
            return $handler->onHandshake(...func_get_args());
        };';

        eval($closure);

        return $closure;
    }
}
