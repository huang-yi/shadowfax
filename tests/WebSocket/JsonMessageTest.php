<?php

namespace HuangYi\Shadowfax\Tests\WebSocket;

use HuangYi\Shadowfax\Exceptions\InvalidMessageException;
use HuangYi\Shadowfax\WebSocket\JsonMessage;
use PHPUnit\Framework\TestCase;

class JsonMessageTest extends TestCase
{
    public function testInvalidJson()
    {
        $this->expectException(InvalidMessageException::class);

        new JsonMessage('{"foo":"bar"');
    }


    public function testGetData()
    {
        $message = new JsonMessage('{"foo":"bar"}');

        $data = $message->getData();

        $this->assertSame(['foo' => 'bar'], $data);
    }
}
