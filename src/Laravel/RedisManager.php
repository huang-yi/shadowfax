<?php

namespace HuangYi\Shadowfax\Laravel;

use Illuminate\Redis\RedisManager as LaravelRedisManager;

class RedisManager extends LaravelRedisManager
{
    use HasConnectionPools;

    /**
     * Create a new Redis manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  string  $driver
     * @param  array  $config
     * @param  array  $poolsConfig
     * @return void
     */
    public function __construct($app, $driver, array $config, array $poolsConfig = [])
    {
        if (version_compare($app->version(), '5.7', '<')) {
            parent::__construct($driver, $config);
        } else {
            parent::__construct($app, $driver, $config);
        }

        $this->poolsConfig = $poolsConfig;
    }

    /**
     * Get a Redis connection by name.
     *
     * @param  string|null  $name
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function connection($name = null)
    {
        $name = $name ?: 'default';

        if (! $this->isPoolConnection($name)) {
            return parent::connection($name);
        }

        if ($connection = $this->getConnectionFromContext($name)) {
            return $connection;
        }

        return $this->getConnectionFromPool($name);
    }

    /**
     * Resolve the connection.
     *
     * @param  string  $name
     * @return \Illuminate\Redis\Connections\Connection
     */
    protected function resolveConnection($name)
    {
        return $this->configure($this->resolve($name), $name);
    }

    /**
     * Get the connection key in coroutine context.
     *
     * @param  string  $name
     * @return string
     */
    protected function getConnectionKeyInContext($name)
    {
        return 'redis.connections.'.$name;
    }
}
