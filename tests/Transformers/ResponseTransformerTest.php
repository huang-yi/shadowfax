<?php

namespace HuangYi\Swoole\Tests\Transformers;

use Illuminate\Http\Response as IlluminateResponse;
use HuangYi\Swoole\Transformers\ResponseTransformer;
use PHPUnit\Framework\TestCase;

class ResponseTransformerTest extends TestCase
{
    public function testMake()
    {
        $responseTransformer = ResponseTransformer::make(new IlluminateResponse);

        $this->assertInstanceOf(ResponseTransformer::class, $responseTransformer);
    }
}
