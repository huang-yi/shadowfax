<?php

namespace HuangYi\Swoole\WebSocket;

use HuangYi\Swoole\Contracts\MessageContract;
use HuangYi\Swoole\Tasks\BroadcastTask;
use Illuminate\Contracts\Container\Container;

class Room
{
    use HasRedis;

    /**
     * Path.
     *
     * @var string
     */
    protected $path;

    /**
     * Route.
     *
     * @var \HuangYi\Swoole\WebSocket\Route
     */
    protected $route;

    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Room.
     *
     * @param string $path
     * @return void
     */
    public function __construct($path)
    {
        $this->path = '/' . ltrim($path, '/');
    }

    /**
     * Join room.
     *
     * @param int $socketId
     * @return $this
     */
    public function join($socketId)
    {
        $roomsKey = $this->roomsKey();
        $roomKey = $this->roomKey($this->path);
        $clientKey = $this->clientKey($socketId);

        $this->redis->sadd($roomsKey, [$this->path]);
        $this->redis->sadd($roomKey, [$socketId]);
        $this->redis->hset($clientKey, 'path', $this->path);

        return $this;
    }

    /**
     * Leave room.
     *
     * @param int $socketId
     * @return $this
     */
    public function leave($socketId)
    {
        $roomKey = $this->roomKey($this->path);
        $clientKey = $this->clientKey($socketId);

        $this->redis->srem($roomKey, $socketId);
        $this->redis->hdel($clientKey, ['path']);

        return $this;
    }

    /**
     * Get clients.
     *
     * @return array
     */
    public function getClients()
    {
        $roomKey = $this->roomKey($this->path);

        return array_map('intval', $this->redis->smembers($roomKey));
    }

    /**
     * Broadcast a message.
     *
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @param array|int $excepts
     * @return void
     */
    public function broadcast(MessageContract $message, $excepts = null)
    {
        $this->container->make('swoole.server')
            ->task(BroadcastTask::make([
                'message' => (string) $message,
                'clients' => $this->getClients(),
                'excepts' => $excepts, 
            ]));
    }

    /**
     * Flush path.
     *
     * @return void
     */
    public function flush()
    {
        $roomsKey = $this->roomsKey();
        $server = $this->container->make('swoole.server');

        foreach ($this->getClients() as $socketId) {
            $socketId = intval($socketId);

            if ($server->exist($socketId)) {
                $server->close($socketId);
            } else {
                $this->leave($socketId);
            }
        }

        $this->redis->srem($roomsKey, $this->path);
    }

    /**
     * Get route.
     *
     * @return \HuangYi\Swoole\WebSocket\Route
     */
    public function getRoute()
    {
        if ($this->route) {
            return $this->route;
        }

        $routes = $this->container->make('swoole.websocket.router')->getRoutes();

        $path = rawurldecode($this->path);

        foreach ($routes as $route) {
            if (preg_match($route->getCompiled()->getRegex(), $path)) {
                $this->route = $route;

                break;
            }
        }

        return $this->route;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set container.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}
