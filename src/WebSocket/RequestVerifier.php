<?php

namespace HuangYi\Shadowfax\WebSocket;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequestVerifier
{
    /**
     * The WebSocket protocol version.
     */
    const VERSION = 13;

    /**
     * The Globally Unique Identifier.
     */
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new RequestVerifier instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Verify the request.
     *
     * @return bool
     */
    public function verify()
    {
        $this->verifyMethod();
        $this->verifyProtocolVersion();
        $this->verifyUpgrade();
        $this->verifyConnection();
        $this->verifyKey();
        $this->verifyVersion();

        return true;
    }

    /**
     * Verify the HTTP method.
     *
     * @return bool
     */
    public function verifyMethod()
    {
        if ($this->request->getMethod() == 'GET') {
            return true;
        }

        throw new HttpException(505);
    }

    /**
     * Verify the HTTP protocol version.
     *
     * @return bool
     */
    public function verifyProtocolVersion()
    {
        if ((float) str_replace('HTTP/', '', $this->request->getProtocolVersion()) >= 1.1) {
            return true;
        }

        throw new HttpException(505);
    }

    /**
     * Verify the Upgrade header.
     *
     * @return bool
     */
    public function verifyUpgrade()
    {
        if (strtolower($this->request->header('Upgrade')) == 'websocket') {
            return true;
        }

        throw new HttpException(426);
    }


    /**
     * Verify the Connection header.
     *
     * @return bool
     */
    public function verifyConnection()
    {
        foreach (explode(';', $this->request->header('Connection')) as $part) {
            foreach (explode(',', trim($part)) as $value) {
                if (strtolower(trim($value)) == 'upgrade') {
                    return true;
                }
            }
        }

        throw new HttpException(400);
    }

    /**
     * Verify the Sec-WebSocket-Key header.
     *
     * @return bool
     */
    public function verifyKey()
    {
        $key = base64_decode($this->request->header('Sec-WebSocket-Key'));

        if (is_string($key) && strlen($key) == 16) {
            return true;
        }

        throw new HttpException(400);
    }

    /**
     * Verify the Sec-WebSocket-Version header.
     *
     * @return bool
     */
    public function verifyVersion()
    {
        if ((int) $this->request->header('Sec-WebSocket-Version') == static::VERSION) {
            return true;
        }

        throw new HttpException(426);
    }

    /**
     * Get the value of Sec-WebSocket-Accept.
     *
     * @return string
     */
    public function getSecWebSocketAccept()
    {
        return base64_encode(sha1($this->request->header('Sec-WebSocket-Key').static::GUID, true));
    }
}
