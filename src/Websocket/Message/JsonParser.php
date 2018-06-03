<?php

namespace HuangYi\Swoole\Websocket\Message;

use HuangYi\Swoole\Websocket\Contracts\MessageContract;
use HuangYi\Swoole\Websocket\Contracts\ParserContract;
use HuangYi\Swoole\Websocket\Exceptions\ParseException;

class JsonParser implements ParserContract
{
    /**
     * Parse message.
     *
     * @param string $payload
     * @return \HuangYi\Swoole\Websocket\Message\Message
     * @throws \HuangYi\Swoole\Websocket\Exceptions\ParseException
     */
    public function parse($payload) : MessageContract
    {
        $message = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ParseException("[$payload] is not a valid json.", 400);
        }

        return new Message($message['event'], $message['data'] ?? null);
    }
}
