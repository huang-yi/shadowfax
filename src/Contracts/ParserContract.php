<?php

namespace HuangYi\Http\Contracts;

interface ParserContract
{
    /**
     * Parse message.
     *
     * @param mixed $payload
     * @return \HuangYi\Http\Contracts\MessageContract $message
     */
    public function parse($payload);
}
