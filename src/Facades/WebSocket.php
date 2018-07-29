<?php

namespace HuangYi\Swoole\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * WebSocket Facade.
 *
 * @see \HuangYi\Swoole\WebSocket\WebSocket
 */
class WebSocket extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'swoole.websocket';
    }
}
