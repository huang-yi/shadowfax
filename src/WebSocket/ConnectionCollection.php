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
     * @return void
     */
    public static function add(Connection $connection)
    {
        static::$connections[$connection->getId()] = $connection;
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

    /**
     * Get all the connections.
     *
     * @return array
     */
    public static function all()
    {
        return static::$connections;
    }
}
