<?php

namespace HuangYi\Shadowfax\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void listen(string $uri, \HuangYi\Shadowfax\Contracts\WebSocket\Handler $handler)
 * @method static \HuangYi\Shadowfax\Contracts\WebSocket\Handler findHandler(\Illuminate\Http\Request $request)
 */
class WebSocket extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shadowfax.websocket';
    }
}
