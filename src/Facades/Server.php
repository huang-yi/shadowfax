<?php

namespace HuangYi\Swoole\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Swoole Server Facade.
 *
 * @see \HuangYi\Swoole\ServerManager
 */
class Server extends Facade
{
    protected static function getFacadeAccessor()
    {
        return static::$app['swoole.server'];
    }
}
