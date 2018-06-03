<?php

namespace HuangYi\Http\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Swoole Server Facade.
 *
 * @see \HuangYi\Http\ServerManager
 */
class Server extends Facade
{
    protected static function getFacadeAccessor()
    {
        return static::$app['swoole.server'];
    }
}
