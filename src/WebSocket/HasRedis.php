<?php

namespace HuangYi\Swoole\WebSocket;

use Closure;
use Illuminate\Redis\Connections\Connection;

trait HasRedis
{
    /**
     * Redis connection.
     *
     * @var \Illuminate\Redis\Connections\Connection
     */
    protected $redis;

    /**
     * Redis prefix.
     *
     * @var string
     */
    protected $redisPrefix;

    /**
     * Set redis.
     *
     * @param \Illuminate\Redis\Connections\Connection $redis
     * @param string $prefix
     * @return $this
     */
    public function setRedis(Connection $redis, $prefix)
    {
        $this->redis = $redis;
        $this->redisPrefix = $prefix;

        return $this;
    }

    /**
     * @param \Closure $callback
     * @return void
     */
    public function redisMulti(Closure $callback)
    {
        $this->redis->multi();

        $callback($this->redis);

        $this->redis->exec();
    }

    /**
     * @param int $socketId
     * @return string
     */
    public function clientKey($socketId)
    {
        return sprintf('%s:clients:%s', $this->redisPrefix, $socketId);
    }

    /**
     * @param string $path
     * @return string
     */
    public function roomKey($path)
    {
        return sprintf('%s:%s', $this->roomsKey(), sha1($path));
    }

    /**
     * @return string
     */
    public function roomsKey()
    {
        return $this->redisPrefix.':rooms';
    }
}
