<?php

namespace HuangYi\Shadowfax\Contracts\WebSocket;

interface Connection
{
    /**
     * Get the socket id.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Get the connection's handler.
     *
     * @return \HuangYi\Shadowfax\Contracts\WebSocket\Handler
     */
    public function getHandler(): Handler;

    /**
     * Send the data to client.
     *
     * @param  mixed  $data
     * @param  bool  $isBinary
     * @return bool
     */
    public function send($data, $isBinary = false);

    /**
     * Send the data to other client.
     *
     * @param  int  $socket
     * @param  mixed  $data
     * @param  bool  $isBinary
     * @return bool
     */
    public function sendTo($socket, $data, $isBinary = false);

    /**
     * Close the connection.
     *
     * @param  int  $code
     * @param  string  $reason
     * @return bool
     */
    public function close($code = 1000, $reason = '');

    /**
     * Close with other client.
     *
     * @param  int  $socket
     * @param  int  $code
     * @param  string  $reason
     * @return mixed
     */
    public function closeWith($socket, $code = 1000, $reason = '');
}
