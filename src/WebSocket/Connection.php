<?php

namespace HuangYi\Shadowfax\WebSocket;

use HuangYi\Shadowfax\Contracts\WebSocket\Connection as ConnectionContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Swoole\WebSocket\Server;

class Connection implements ConnectionContract
{
    /**
     * The socket id.
     *
     * @var int
     */
    protected $id;

    /**
     * The WebSocket server instance.
     *
     * @var \Swoole\WebSocket\Server
     */
    protected $server;

    /**
     * WebSocket Connection.
     *
     * @param  int  $id
     * @param  \Swoole\WebSocket\Server  $server
     * @return void
     */
    public function __construct(int $id, Server $server)
    {
        $this->id = $id;
        $this->server = $server;
    }

    /**
     * Get the socket id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the WebSocket server instance.
     *
     * @return \Swoole\WebSocket\Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * Send the data to client.
     *
     * @param  mixed  $data
     * @param  int  $isBinary
     * @return bool
     */
    public function send($data, $isBinary = 0)
    {
        return $this->sendTo($this->id, $data, $isBinary);
    }

    /**
     * Send the data to other connection.
     *
     * @param  int  $socket
     * @param  mixed  $data
     * @param  int  $isBinary
     * @return bool
     */
    public function sendTo($socket, $data, $isBinary = 0)
    {
        if (! $this->server->isEstablished($socket)) {
            return false;
        }

        if (is_array($data)) {
            $data = json_encode($data);
        } elseif ($data instanceof Arrayable) {
            $data = json_encode($data->toArray());
        } elseif ($data instanceof Jsonable) {
            $data = $data->toJson();
        } elseif (! is_string($data)) {
            $data = (string) $data;
        }

        return $this->server->push($socket, $data, $isBinary ? 2 : 1);
    }

    /**
     * Close the connection.
     *
     * @param  int  $code
     * @param  string  $reason
     * @return bool
     */
    public function close($code = 1000, $reason = '')
    {
        return $this->closeWith($this->getId());
    }

    /**
     * Close with other connection.
     *
     * @param  int  $socket
     * @param  int  $code
     * @param  string  $reason
     * @return bool
     */
    public function closeWith($socket, $code = 1000, $reason = '')
    {
        if ($this->server->isEstablished($socket)) {
            return $this->server->disconnect($socket, $code, $reason);
        }

        return true;
    }
}
