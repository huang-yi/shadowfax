<?php

namespace HuangYi\Swoole\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * WebSocket Facade.
 *
 * @method static \HuangYi\Swoole\WebSocket\Route room(string $uri, mixed $action = null)
 * @method static \HuangYi\Swoole\WebSocket\WebSocket joinRoom(\Illuminate\Http\Request $request)
 * @method static \HuangYi\Swoole\WebSocket\Room getClientRoom(int $socketId)
 * @method static \HuangYi\Swoole\WebSocket\Room getRoom(string $path)
 * @method static void emit(int $socketId, \HuangYi\Swoole\Contracts\MessageContract $message)
 * @method static void broadcast(\HuangYi\Swoole\Contracts\MessageContract $message, mixed $excepts = null)
 * @method static void flush()
 * @method static \HuangYi\Swoole\WebSocket\WebSocket setDefaultRoom(string $uri)
 * @method static \HuangYi\Swoole\WebSocket\Route connected(mixed $action)
 * @method static \HuangYi\Swoole\WebSocket\Route on(string $event, mixed $callback)
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
