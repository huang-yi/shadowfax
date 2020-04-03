<?php

namespace HuangYi\Shadowfax\WebSocket;

use HuangYi\Shadowfax\Contracts\WebSocket\Connection;

class ConnectionCollection
{
    /**
     * The connections.
     *
     * @var array
     */
    protected static $connections = [];

    /**
     * Add a connection.
     *
     * @param  \HuangYi\Shadowfax\Contracts\WebSocket\Connection  $connection
     * @param  \HuangYi\Shadowfax\Contracts\WebSocket\Handler  $handler
     * @return void
     */
    public static function add(Connection $connection, $handler)
    {
        static::$connections[$connection->getId()] = [$connection, $handler];
    }

    /**
     * Forget a connection.
     *
     * @param  int  $socketId
     * @return void
     */
    public static function forget($socketId)
    {
        unset(static::$connections[$socketId]);
    }

    /**
     * Find a connection.
     *
     * @param  int  $socketId
     * @return \HuangYi\Shadowfax\Contracts\WebSocket\Connection
     */
    public static function find(int $socketId)
    {
        return static::$connections[$socketId] ?? null;
    }
}
