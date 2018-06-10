<?php

namespace HuangYi\Swoole\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Swoole Table Facade.
 *
 * @method static \Swoole\Table create(array $config)
 * @method static \Swoole\Table use(string $name)
 * @method static bool truncate(string $name)
 *
 * @see \HuangYi\Swoole\TableCollection
 */
class Table extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'swoole.tables';
    }
}
