<?php

namespace HuangYi\Swoole\Contracts;

use Swoole\Websocket\Frame;

interface ParserContract
{
    /**
     * Parse message.
     *
     * @param \Swoole\Websocket\Frame $frame
     * @return \HuangYi\Swoole\Contracts\MessageContract $message
     */
    public function parse(Frame $frame);
}
