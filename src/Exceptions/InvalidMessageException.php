<?php

namespace HuangYi\Shadowfax\Exceptions;

class InvalidMessageException extends WebSocketException
{
    public function __construct($message = "Invalid message.", $code = 1007)
    {
        parent::__construct($message, $code);
    }
}
