<?php

namespace HuangYi\Swoole\WebSocket;

use HuangYi\Swoole\Contracts\MessageContract;
use HuangYi\Swoole\Tasks\BroadcastTask;
use HuangYi\Swoole\Tasks\EmitTask;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Redis\Connections\Connection;

/**
 * @mixin \HuangYi\Swoole\WebSocket\Route
 */
class WebSocket
{
    use HasRedis;

    /**
     * Default room.
     *
     * @var string
     */
    protected $defaultRoom = '/';

    /**
     * Rooms.
     *
     * @var array
     */
    protected $rooms = [];

    /**
     * Container.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * WebSocket.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Define a kind of room.
     *
     * @param string $uri
     * @param mixed $action
     * @return \HuangYi\Swoole\WebSocket\Route
     */
    public function room($uri, $action = null)
    {
        return $this->container
            ->make('swoole.websocket.router')
            ->room($uri, $action);
    }

    /**
     * Join room.
     *
     * @param \Illuminate\Http\Request $request
     * @return $this
     */
    public function joinRoom(Request $request)
    {
        $socketId = $this->container->make('swoole.http.request')->fd;

        $this->getRoom($request->path())->join($socketId);

        return $this;
    }

    /**
     * Get client room.
     *
     * @param int $socketId
     * @return \HuangYi\Swoole\WebSocket\Room|null
     */
    public function getClientRoom($socketId)
    {
        $clientKey = $this->clientKey($socketId);
        $path = $this->redis->hget($clientKey, 'path');

        if (! $path) {
            return null;
        }

        return $this->getRoom($path);
    }

    /**
     * Get room.
     *
     * @param string $path
     * @return \HuangYi\Swoole\WebSocket\Room
     */
    public function getRoom($path)
    {
        $path = '/' . ltrim($path, '/');

        if (isset($this->rooms[$path])) {
            return $this->rooms[$path];
        }

        $room = new Room($path);

        $room->setContainer($this->container)
            ->setRedis($this->redis, $this->redisPrefix);

        return $this->rooms[$room->getPath()] = $room;
    }

    /**
     * Emit a message.
     *
     * @param int $socketId
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @return void
     */
    public function emit($socketId, MessageContract $message)
    {
        $this->container->make('swoole.server')
            ->task(EmitTask::make([
                'message' => (string) $message,
                'to' => $socketId,
            ]));
    }

    /**
     * Broadcast a message.
     *
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @param array $excepts
     * @return void
     */
    public function broadcast(MessageContract $message, $excepts = null)
    {
        $this->container->make('swoole.server')
            ->task(BroadcastTask::make([
                'message' => (string) $message,
                'excepts' => $excepts,
            ]));
    }

    /**
     * Flush rooms.
     *
     * @return void
     */
    public function flush()
    {
        $this->redis->del($this->redis->keys($this->redisPrefix));
    }

    /**
     * Set default room.
     *
     * @param string $uri
     * @return $this
     */
    public function setDefaultRoom($uri)
    {
        $this->defaultRoom = $uri;

        return $this;
    }

    /**
     * Call default room.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $defaultRoomRoute = $this->getRoom($this->defaultRoom)->getRoute();

        return call_user_func_array([$defaultRoomRoute, $method], $arguments);
    }
}
