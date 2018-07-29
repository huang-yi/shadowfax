<?php

namespace HuangYi\Swoole\Tests\WebSocket;

use HuangYi\Swoole\Exceptions\MessageParseException;
use HuangYi\Swoole\WebSocket\JsonParser;
use HuangYi\Swoole\WebSocket\Message;
use PHPUnit\Framework\TestCase;
use Swoole\Websocket\Frame;

class JsonParserTest extends TestCase
{
    public function testParse()
    {
        $message = (new JsonParser)->parse(new ValidFrame);

        $this->assertInstanceOf(Message::class, $message);
    }

    public function testParseInvalidJson()
    {
        $this->expectException(MessageParseException::class);

        (new JsonParser)->parse(new InvalidJsonFrame);
    }

    public function testParseInvalidEvent()
    {
        $this->expectException(MessageParseException::class);

        (new JsonParser)->parse(new NoEventFrame);
    }
}

class ValidFrame extends Frame
{
    public $fd = 1;
    public $data = '{"event":"join"}';
}

class InvalidJsonFrame extends Frame
{
    public $fd = 1;
    public $data = '{"event":"join"';
}

class NoEventFrame extends Frame
{
    public $fd = 1;
    public $data = '{"data":"hello"}';
}
