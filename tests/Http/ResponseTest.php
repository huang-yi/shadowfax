<?php

namespace HuangYi\Shadowfax\Tests\Http;

use Illuminate\Http\Response as IlluminateResponse;
use HuangYi\Shadowfax\Http\Response;
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Swoole\Http\Response as SwooleResponse;

class ResponseTest extends TestCase
{
    public function testMake()
    {
        $response = Response::make(new IlluminateResponse);

        $this->assertInstanceOf(Response::class, $response);
    }


    public function testFiredAfterSendingCallbacks()
    {
        $response = new Response(new IlluminateResponse);

        $sended = false;

        $response->afterSending(function () use (&$sended) {
            $sended = true;
        });

        $response->send($this->mockSwooleResponse());

        $this->assertTrue($sended);
    }


    protected function mockSwooleResponse()
    {
        $response = m::mock(SwooleResponse::class);

        $response->shouldReceive('header');
        $response->shouldReceive('status');
        $response->shouldReceive('end');

        return $response;
    }
}
