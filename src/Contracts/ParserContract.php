<?php

namespace HuangYi\Swoole\Contracts;

interface ParserContract
{
    /**
     * Parse message.
     *
     * @param mixed $payload
     * @return \HuangYi\Swoole\Contracts\MessageContract $message
     */
    public function parse($payload);
}
