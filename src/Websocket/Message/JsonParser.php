<?php

namespace HuangYi\Http\Websocket\Message;

use HuangYi\Http\Contracts\ParserContract;
use HuangYi\Http\Exceptions\MessageParseException;

class JsonParser implements ParserContract
{
    /**
     * Parse message.
     *
     * @param string $payload
     * @return \HuangYi\Http\Websocket\Message\Message
     * @throws \HuangYi\Http\Exceptions\MessageParseException
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
