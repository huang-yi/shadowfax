<?php

namespace HuangYi\Swoole\Tests\Transformers;

use Illuminate\Http\Request as IlluminateRequest;
use HuangYi\Swoole\Transformers\RequestTransformer;
use PHPUnit\Framework\TestCase;
use Swoole\Http\Request as SwooleHttpRequest;

class RequestTransformerTest extends TestCase
{
    public function testMake()
    {
        $transformer = RequestTransformer::make(new SwooleHttpRequestStub);

        $this->assertInstanceOf(RequestTransformer::class, $transformer);
    }

    public function testToIlluminateRequest()
    {
        $transformer = new RequestTransformer(new SwooleHttpRequestStub);
        $illuminateRequest = $transformer->toIlluminateRequest();

        $this->assertInstanceOf(IlluminateRequest::class, $illuminateRequest);
    }
}

class SwooleHttpRequestStub extends SwooleHttpRequest
{
    public $get = [];
    public $post = [];
    public $header = [];
    public $server = [];
    public $cookie = [];
    public $files = [];
    public $fd = 1;

    function rawContent()
    {
        return 'foo=bar';
    }
}
