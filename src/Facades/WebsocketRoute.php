<?php

namespace HuangYi\Http\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Websocket Route Facade.
 *
 * @method static void on(string $event, array|string|\Closure $action)
 *
 * @see \HuangYi\Http\Websocket\Message\Router
 */
class WebsocketRoute extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'swoole.websocket.router';
    }
}
