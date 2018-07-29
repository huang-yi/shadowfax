<?php

namespace HuangYi\Swoole\Tests\WebSocket;

use HuangYi\Swoole\WebSocket\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * @var \HuangYi\Swoole\WebSocket\Message
     */
    protected $message;

    public function setUp()
    {
        parent::setUp();

        $this->message = new Message('test', ['hello world']);
    }

    public function testGetEvent()
    {
        $this->assertEquals('test', $this->message->getEvent());
    }

    public function testGetData()
    {
        $this->assertEquals(['hello world'], $this->message->getData());
    }

    public function testSocketId()
    {
        $this->message->setSocketId(1);

        $this->assertEquals(1, $this->message->getSocketId());
    }

    public function testToArray()
    {
        $this->assertEquals(['event' => 'test', 'data' => ['hello world']], $this->message->toArray());
    }

    public function testToJson()
    {
        $this->assertEquals('{"event":"test","data":["hello world"]}', $this->message->toJson());
    }

    public function testToString()
    {
        $this->assertEquals('{"event":"test","data":["hello world"]}', (string) $this->message);
    }
}
