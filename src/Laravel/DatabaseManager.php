<?php

namespace HuangYi\Shadowfax\Laravel;

use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager as LaravelDatabaseManager;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class DatabaseManager extends LaravelDatabaseManager
{
    /**
     * The pools configuration.
     *
     * @var array
     */
    protected $poolsConfig;

    /**
     * The database pools.
     *
     * @var array
     */
    protected static $pools = [];

    /**
     * The callback to be executed to reconnect to a database in pool.
     *
     * @var callable
     */
    protected $poolReconnector;

    /**
     * Create a new DatabaseManager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Database\Connectors\ConnectionFactory  $factory
     * @param  array  $poolsConfig
     * @return void
     */
    public function __construct($app, ConnectionFactory $factory, array $poolsConfig = [])
    {
        parent::__construct($app, $factory);

        $this->poolsConfig = $poolsConfig;

        $this->poolReconnector = function ($connection) {
            $this->poolReconnect($connection);
        };
    }

    /**
     * Get a database connection instance.
     *
     * @param  string|null  $name
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function connection($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();

        if (! $this->inCoroutine() ||
            ! $this->isPoolConnection($name) ||
            ! $this->isConnectionDriverSupportPool($name)
        ) {
            return parent::connection($name);
        }

        if ($connection = $this->getConnectionFromContext($name)) {
            return $connection;
        }

        return $this->getConnectionFromPool($name);
    }

    /**
     * Get the connection from pool.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Connection
     */
    protected function getConnectionFromPool($name)
    {
        if (! isset(static::$pools[$name])) {
            $this->initializePool($name);
        }

        $pool = static::$pools[$name];

        $connection = $pool->pop();

        $this->setConnectionToContext($name, $connection);

        Coroutine::defer(function () use ($pool, $connection) {
            $pool->push($connection);
        });

        return $connection;
    }

    /**
     * Initialize the connection pool.
     *
     * @param  string  $name
     * @return void
     */
    protected function initializePool($name)
    {
        $capacity = (int) $this->poolsConfig[$name];

        $pool = new Channel($capacity);

        for ($i = 0; $i < $capacity; $i++) {
            [$database, $type] = $this->parseConnectionName($name);

            $connection = $this->configure(
                $this->makeConnection($database), $type
            );

            $connection->setReconnector($this->poolReconnector);

            $pool->push($connection);
        }

        static::$pools[$name] = $pool;
    }

    /**
     * Get the connection from coroutine context.
     *
     * @param  string  $name
     * @param  int  $cid
     * @return \Illuminate\Database\Connection|null
     */
    protected function getConnectionFromContext($name, $cid = null)
    {
        if (in_array($cid, [-1, false], true)) {
            return null;
        }

        $key = $this->getConnectionKeyInContext($name);

        if (! $connection = Coroutine::getContext($cid)[$key] ?? null) {
            return $this->getConnectionFromContext($name, Coroutine::getPcid($cid));
        }

        return $connection;
    }

    /**
     * Set the connection to coroutine context.
     *
     * @param  string  $name
     * @param  \Illuminate\Database\Connection  $connection
     * @return void
     */
    protected function setConnectionToContext($name, Connection $connection)
    {
        $key = $this->getConnectionKeyInContext($name);

        Coroutine::getContext()[$key] = $connection;
    }

    /**
     * Get the connection key in coroutine context.
     *
     * @param  string  $name
     * @return string
     */
    protected function getConnectionKeyInContext($name)
    {
        return 'db.connections.'.$name;
    }

    /**
     * Determine if run in coroutine.
     *
     * @return bool
     */
    protected function inCoroutine()
    {
        return Coroutine::getCid() !== -1;
    }

    /**
     * Determine if the connection is a connection in pool.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isPoolConnection($name)
    {
        $name = $name ?: $this->getDefaultConnection();

        return isset($this->poolsConfig[$name]);
    }

    /**
     * Determine if the connection driver support pool.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isConnectionDriverSupportPool($name)
    {
        $config = $this->configuration($name);

        return in_array($config['driver'], ['mysql']);
    }

    /**
     * Reconnect to the given database.
     *
     * @param  string|null  $name
     * @return \Illuminate\Database\Connection
     */
    public function reconnect($name = null)
    {
        if (! $this->isPoolConnection($name)) {
            return parent::reconnect($name);
        }
    }

    /**
     * Reconnect the connection in pool.
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return \Illuminate\Database\Connection
     */
    public function poolReconnect(Connection $connection)
    {
        $connection->disconnect();

        $fresh = $this->makeConnection($connection->getName());

        return $connection
            ->setPdo($fresh->getRawPdo())
            ->setReadPdo($fresh->getRawReadPdo());
    }
}
