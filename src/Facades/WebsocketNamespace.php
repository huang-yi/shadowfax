<?php

namespace HuangYi\Http\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Swoole Table Facade.
 *
 * @method static void join(string $path, int $socketId)
 * @method static void leave(string $path, int $socketId)
 * @method static void broadcast(string $path, \HuangYi\Http\Contracts\MessageContract $message, array|int|null $excepts)
 * @method static void emit(int $socketId, \HuangYi\Http\Contracts\MessageContract $message)
 * @method static \Swoole\Server getServer()
 * @method static string getPath(int $socketId)
 * @method static array getClients(string $path)
 * @method static bool truncate(string $name)
 *
 * @see \HuangYi\Http\Websocket\NamespaceManager
 */
class WebsocketNamespace extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'swoole.websocket.namespace';
    }
}
