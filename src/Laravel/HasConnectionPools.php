<?php

namespace HuangYi\Shadowfax\Laravel;

use RuntimeException;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

trait HasConnectionPools
{
    /**
     * The pools configuration.
     *
     * @var array
     */
    protected $poolsConfig;

    /**
     * The redis pools.
     *
     * @var array
     */
    protected static $pools = [];

    /**
     * Get the connection from pool.
     *
     * @param  string  $name
     * @return mixed
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
            $pool->push($this->resolveConnection($name));
        }

        static::$pools[$name] = $pool;
    }

    /**
     * Resolve the connection.
     *
     * @param  string  $name
     * @return mixed
     */
    protected function resolveConnection($name)
    {
        throw new RuntimeException('You need to implement resolveConnection method.');
    }

    /**
     * Get the connection from coroutine context.
     *
     * @param  string  $name
     * @param  int  $cid
     * @return mixed
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
     * @param  mixed  $connection
     * @return void
     */
    protected function setConnectionToContext($name, $connection)
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
        return $name;
    }

    /**
     * Determine if the connection is a pool connection.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isPoolConnection($name)
    {
        return Coroutine::getCid() !== -1 && isset($this->poolsConfig[$name]);
    }
}
