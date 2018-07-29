<?php

namespace HuangYi\Swoole\WebSocket;

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
     * Set redis.
     *
     * @param \Illuminate\Redis\Connections\Connection $redis
     * @return $this
     */
    public function setRedis(Connection $redis)
    {
        $this->redis = $redis;

        return  $this;
    }

    /**
     * @param int $socketId
     * @return string
     */
    public function clientKey($socketId)
    {
        return sprintf('websocket:clients:%s', $socketId);
    }

    /**
     * @param string $path
     * @return string
     */
    public function roomKey($path)
    {
        return sprintf('websocket:rooms:%s', sha1($path));
    }

    /**
     * @return string
     */
    public function roomsKey()
    {
        return 'websocket:rooms';
    }
}
