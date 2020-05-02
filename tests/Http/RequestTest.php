<?php

namespace HuangYi\Shadowfax\Tests\Http;

use HuangYi\Shadowfax\Http\Request;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
use Swoole\Http\Request as SwooleRequest;

class RequestTest extends TestCase
{
    public function testMake()
    {
        $request = Request::make(new SwooleRequest);

        $this->assertInstanceOf(Request::class, $request);
    }


    public function testRequest()
    {
        $swooleRequest = new SwooleRequest();

        $swooleRequest->get = ['get_key' => 'v1'];
        $swooleRequest->post = ['post_key' => 'v2'];
        $swooleRequest->server = ['SERVER_KEY' => 'v3', 'REQUEST_METHOD' => 'POST'];
        $swooleRequest->header = ['X-KEY' => 'v4'];
        $swooleRequest->cookie = ['cookie_key' => 'v5'];
        $swooleRequest->files = ['file_key' => ['error' => 0, 'name' => '', 'size' => '', 'tmp_name' => __FILE__, 'type' => '']];

        $request = new Request($swooleRequest);

        $illuminateRequest = $request->getIlluminateRequest();

        $this->assertInstanceOf(IlluminateRequest::class, $illuminateRequest);
        $this->assertSame('v1', $illuminateRequest->query('get_key'));
        $this->assertSame('v2', $illuminateRequest->post('post_key'));
        $this->assertSame('v3', $illuminateRequest->server('SERVER_KEY'));
        $this->assertSame('v4', $illuminateRequest->header('X-KEY'));
        $this->assertSame('v5', $illuminateRequest->cookie('cookie_key'));
        $this->assertInstanceOf(UploadedFile::class, $illuminateRequest->file('file_key'));
    }
}
