<?php

namespace HuangYi\Swoole\Websocket\Message;

use HuangYi\Swoole\Contracts\ParserContract;
use HuangYi\Swoole\Exceptions\MessageParseException;

class JsonParser implements ParserContract
{
    /**
     * Parse message.
     *
     * @param string $payload
     * @return \HuangYi\Swoole\Websocket\Message\Message
     * @throws \HuangYi\Swoole\Exceptions\MessageParseException
     */
    public function parse($payload)
    {
        $message = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new MessageParseException("[$payload] is not a valid json.");
        }

        return new Message($message['event'], $message['data'] ?? null);
    }
}
