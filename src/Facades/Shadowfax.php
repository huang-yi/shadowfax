<?php

namespace HuangYi\Shadowfax\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed make(string $abstract)
 * @method static \HuangYi\Shadowfax\Shadowfax instance(string $abstract, mixed $instance)
 */
class Shadowfax extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shadowfax';
    }
}
