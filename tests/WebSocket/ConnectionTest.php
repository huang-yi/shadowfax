<?php

namespace HuangYi\Shadowfax\Tests\WebSocket;

use HuangYi\Shadowfax\WebSocket\Connection;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Swoole\Process;
use Swoole\WebSocket\Server;

class ConnectionTest extends TestCase
{
    public function testSendToSuccessful()
    {
        $socketId = 1;
        $data = 'message';
        $isBinary = false;
        $opcode = WEBSOCKET_OPCODE_TEXT;

        $server = m::mock(WebSocketServer::class);
        $server->shouldReceive('isEstablished')->once()->with($socketId)->andReturn(true);
        $server->shouldReceive('push')->once()->with($socketId, $data, $opcode)->andReturn(true);

        $connection = new Connection($socketId, $server, new EmptyHandler);

        $result = $connection->sendTo($socketId, $data, $isBinary);

        $this->assertTrue($result);
    }


    public function testSendToFailed()
    {
        $socketId = 1;
        $data = 'message';
        $isBinary = false;

        $server = m::mock(WebSocketServer::class);
        $server->shouldReceive('isEstablished')->once()->with($socketId)->andReturn(false);

        $connection = new Connection($socketId, $server, new EmptyHandler);

        $result = $connection->sendTo($socketId, $data, $isBinary);

        $this->assertFalse($result);
    }


    public function testCloseWith()
    {
        $socketId = 1;
        $code = 100;
        $reason = '';

        $server = m::mock(WebSocketServer::class);
        $server->shouldReceive('isEstablished')->once()->with($socketId)->andReturn(true);
        $server->shouldReceive('disconnect')->once()->with($socketId, $code, $reason)->andReturn(true);

        $connection = new Connection($socketId, $server, new EmptyHandler);

        $result = $connection->closeWith($socketId, $code, $reason);

        $this->assertTrue($result);
    }
}

class WebSocketServer extends Server
{
    public function addProcess(Process $process)
    {
        parent::addProcess($process);
    }
}
