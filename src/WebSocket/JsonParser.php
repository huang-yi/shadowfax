<?php

namespace HuangYi\Swoole\WebSocket;

use HuangYi\Swoole\Contracts\ParserContract;
use HuangYi\Swoole\Exceptions\MessageParseException;
use Swoole\Websocket\Frame;

class JsonParser implements ParserContract
{
    /**
     * Parse frame.
     *
     * @param \Swoole\Websocket\Frame $frame
     * @return \HuangYi\Swoole\WebSocket\Message
     * @throws \HuangYi\Swoole\Exceptions\MessageParseException
     */
    public function parse(Frame $frame)
    {
        $message = json_decode($frame->data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new MessageParseException("[{$frame->data}] is not a valid json.");
        }

        if (! isset($message['event'])) {
            throw new MessageParseException("The message must have an 'event' attribute.");
        }

        $message = new Message($message['event'], array_get($message, 'data'));

        $message->setSocketId($frame->fd);

        return $message;
    }
}
