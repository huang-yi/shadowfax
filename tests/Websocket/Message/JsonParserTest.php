<?php

namespace HuangYi\Swoole\Tests\Websocket\Message;

use HuangYi\Swoole\Exceptions\MessageParseException;
use HuangYi\Swoole\Websocket\Message\JsonParser;
use HuangYi\Swoole\Websocket\Message\Message;
use PHPUnit\Framework\TestCase;

class JsonParserTest extends TestCase
{
    public function testParse()
    {
        $message = (new JsonParser)->parse('{"event":"join"}');

        $this->assertInstanceOf(Message::class, $message);
    }

    public function testParseInvalidJson()
    {
        $this->expectException(MessageParseException::class);

        (new JsonParser)->parse('{"event":"join"');
    }

    public function testParseInvalidEvent()
    {
        $this->expectException(MessageParseException::class);

        (new JsonParser)->parse('{"data":"hello"}');
    }
}
