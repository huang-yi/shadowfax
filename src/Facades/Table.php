<?php

namespace HuangYi\Http\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Swoole Table Facade.
 *
 * @method static \Swoole\Table|null use(string $name)
 * @method static bool truncate(string $name)
 *
 * @see \HuangYi\Http\TableManager
 */
class Table extends Facade
{
    protected static function getFacadeAccessor()
    {
        return static::$app['swoole.server']->tables;
    }
}
