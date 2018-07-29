<?php

namespace HuangYi\Swoole\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * WebSocket Facade.
 *
 * @see \HuangYi\Swoole\WebSocket\WebSocket
 */
class Websocket extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'swoole.websocket';
    }
}
