<?php

namespace HuangYi\Shadowfax\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \HuangYi\Shadowfax\Task\Dispatcher dispatch(\HuangYi\Shadowfax\Contracts\Task $task)
 */
class Task extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shadowfax.task';
    }
}
