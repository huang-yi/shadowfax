<?php

namespace HuangYi\Swoole\Websocket;

use HuangYi\Swoole\Contracts\MessageContract;
use HuangYi\Swoole\Tasks\BroadcastTask;
use HuangYi\Swoole\Tasks\EmitTask;
use Illuminate\Contracts\Container\Container;

class NamespaceManager
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * NamespaceManager.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Join namespace.
     *
     * @param string $path
     * @param int $socketId
     * @return void
     */
    public function join($path, $socketId)
    {
        $path = $this->formatPath($path);
        $pathsKey = $this->getPathsKey();
        $pathKey = $this->getPathKey($path);
        $clientKey = $this->getClientKey($socketId);

        $this->getStore()->sadd($pathsKey, $path);
        $this->getStore()->sadd($pathKey, $socketId);
        $this->getStore()->hset($clientKey, 'path', $path);
    }

    /**
     * Leave namespace.
     *
     * @param int $socketId
     * @return void
     */
    public function leave($socketId)
    {
        $path = $this->getPath($socketId);
        $pathKey = $this->getPathKey($path);
        $clientKey = $this->getClientKey($socketId);

        $this->getStore()->srem($pathKey, $socketId);
        $this->getStore()->hdel($clientKey, 'path');
    }

    /**
     * Flush all paths.
     *
     * @return void
     */
    public function flushAll()
    {
        $this->getStore()->multi();

        foreach ($this->getPaths() as $path) {
            $this->flush($path);
        }

        $this->getStore()->exec();
    }

    /**
     * Flush path.
     *
     * @param string $path
     * @return void
     */
    public function flush($path)
    {
        $path = $this->formatPath($path);

        foreach ($this->getClients($path) as $socketId) {
            $this->getServer()->close($socketId);
        }

        $this->getStore()->srem($this->getPathsKey(), $path);
    }

    /**
     * Broadcast.
     *
     * @param string $path
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @param array|int $excepts
     * @return void
     */
    public function broadcast($path, MessageContract $message, $excepts = null)
    {
        $path = $this->formatPath($path);
        $clients = $this->getClients($path);

        if (! is_null($excepts)) {
            $excepts = array_map(function ($socketId) {
                return (int) $socketId;
            }, (array) $excepts);

            $clients = array_values(array_diff($clients, (array) $excepts));
        }

        $this->getServer()->task(BroadcastTask::make([
            'message' => (string) $message,
            'clients' => $clients,
        ]));
    }

    /**
     * Emit message.
     *
     * @param int $socketId
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @return void
     */
    public function emit($socketId, MessageContract $message)
    {
        $this->getServer()->task(EmitTask::make([
            'message' => (string) $message,
            'to' => $socketId,
        ]));
    }

    /**
     * Get swoole server.
     *
     * @return \Swoole\Server
     */
    public function getServer()
    {
        return $this->container['swoole.server'];
    }

    /**
     * Get path.
     *
     * @param int $socketId
     * @return string
     */
    public function getPath($socketId)
    {
        $key = $this->getClientKey($socketId);

        return $this->getStore()->hget($key, 'path');
    }

    /**
     * Get paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->getStore()->smembers($this->getPathsKey());
    }

    /**
     * Get clients.
     *
     * @param string $path
     * @return array
     */
    public function getClients($path)
    {
        $key = $this->getPathKey($path);

        $clients = $this->getStore()->smembers($key);

        return array_map(function ($socketId) {
            return (int) $socketId;
        }, $clients);
    }

    /**
     * Format path.
     *
     * @param string $path
     * @return string
     */
    public function formatPath($path)
    {
        return '/' . trim($path, '/');
    }

    /**
     * @return string
     */
    public function getPathsKey()
    {
        return 'websocket:paths';
    }

    /**
     * @param string $path
     * @return string
     */
    public function getPathKey($path)
    {
        return sprintf('websocket:paths:%s', sha1($path));
    }

    /**
     * @param int $socketId
     * @return string
     */
    public function getClientKey($socketId)
    {
        return sprintf('websocket:clients:%s', $socketId);
    }

    /**
     * Get redis connection.
     *
     * @return \Illuminate\Redis\Connections\Connection
     */
    protected function getStore()
    {
        $connection = $this->container['config']->get(
            'swoole.namespace_redis', 'default'
        );

        return $this->container['redis']->connection($connection);
    }
}
