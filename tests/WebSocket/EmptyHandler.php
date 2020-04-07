<?php

namespace HuangYi\Shadowfax\Tests\WebSocket;

use HuangYi\Shadowfax\Contracts\WebSocket\Connection;
use HuangYi\Shadowfax\Contracts\WebSocket\Handler;
use HuangYi\Shadowfax\Contracts\WebSocket\Message;
use Illuminate\Http\Request;

class EmptyHandler implements Handler
{
    public function onOpen(Connection $connection, Request $request)
    {
    }

    public function onMessage(Connection $connection, Message $message)
    {
    }

    public function onClose(Connection $connection)
    {
    }
}
