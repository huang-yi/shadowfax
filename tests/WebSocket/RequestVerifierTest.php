<?php

namespace HuangYi\Shadowfax\Tests\WebSocket;

use HuangYi\Shadowfax\WebSocket\RequestVerifier;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequestVerifierTest extends TestCase
{
    public function testVerifyMethod()
    {
        $request = Request::create('/', 'GET');

        $verifier = new RequestVerifier($request);

        $this->assertTrue($verifier->verifyMethod());
    }


    public function testVerifyMethodFailed()
    {
        $this->expectException(HttpException::class);

        $request = Request::create('/', 'POST');

        $verifier = new RequestVerifier($request);

        $verifier->verifyMethod();
    }


    public function testVerifyProtocolVersion()
    {
        $request = Request::create('/', 'GET', [], [], [], ['SERVER_PROTOCOL' => 'HTTP/1.1']);

        $verifier = new RequestVerifier($request);

        $this->assertTrue($verifier->verifyProtocolVersion());
    }


    public function testVerifyProtocolVersionFailed()
    {
        $this->expectException(HttpException::class);

        $request = Request::create('/', 'GET', [], [], [], ['SERVER_PROTOCOL' => 'HTTP/1.0']);

        $verifier = new RequestVerifier($request);

        $verifier->verifyProtocolVersion();
    }


    public function testVerifyUpgrade()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_UPGRADE' => 'websocket']);

        $verifier = new RequestVerifier($request);

        $this->assertTrue($verifier->verifyUpgrade());
    }


    public function testVerifyUpgradeFailed()
    {
        $this->expectException(HttpException::class);

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_UPGRADE' => 'nonsense']);

        $verifier = new RequestVerifier($request);

        $verifier->verifyUpgrade();
    }


    public function testVerifyConnection()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_CONNECTION' => 'Upgrade']);

        $verifier = new RequestVerifier($request);

        $this->assertTrue($verifier->verifyConnection());
    }


    public function testVerifyConnectionFailed()
    {
        $this->expectException(HttpException::class);

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_CONNECTION' => 'nonsense']);

        $verifier = new RequestVerifier($request);

        $verifier->verifyConnection();
    }


    public function testVerifyKey()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_SEC_WEBSOCKET_KEY' => $this->generateKey()]);

        $verifier = new RequestVerifier($request);

        $this->assertTrue($verifier->verifyKey());
    }


    public function testVerifyKeyFailed()
    {
        $this->expectException(HttpException::class);

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_SEC_WEBSOCKET_KEY' => 'invalid key']);

        $verifier = new RequestVerifier($request);

        $verifier->verifyConnection();
    }


    public function testVerifyVersion()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_SEC_WEBSOCKET_VERSION' => 13]);

        $verifier = new RequestVerifier($request);

        $this->assertTrue($verifier->verifyVersion());
    }


    public function testVerifyVersionFailed()
    {
        $this->expectException(HttpException::class);

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_SEC_WEBSOCKET_VERSION' => 1]);

        $verifier = new RequestVerifier($request);

        $verifier->verifyVersion();
    }


    public function testGetSecWebSocketAccept()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_SEC_WEBSOCKET_KEY' => $key = $this->generateKey()]);

        $verifier = new RequestVerifier($request);

        $this->assertEquals(base64_encode(sha1($key.RequestVerifier::GUID, true)), $verifier->getSecWebSocketAccept());
    }


    protected function generateKey()
    {
        $chars = [];

        for ($i = 0; $i < 16; $i++) {
            $chars[] = chr(random_int(65, 122));
        }

        return base64_encode(implode('', $chars));
    }
}
