<?php

namespace HuangYi\Shadowfax\Http;

use Closure;
use Illuminate\Http\Response as IlluminateResponse;
use ReflectionObject;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response
{
    /**
     * The buffer output size.
     *
     * @var int
     */
    protected static $bufferOutputSize = 2 * 1024 * 1024;

    /**
     * The illuminate http response.
     *
     * @var \Illuminate\Http\Response
     */
    protected $illuminateResponse;

    /**
     * All of the after sending callbacks.
     *
     * @var array
     */
    protected $afterSendingCallbacks = [];

    /**
     * Make a new Response.
     *
     * @param  mixed  $illuminateResponse
     * @return static
     */
    public static function make($illuminateResponse)
    {
        return new static($illuminateResponse);
    }

    /**
     * Create a new Response.
     *
     * @param  mixed  $illuminateResponse
     * @return void
     */
    public function __construct($illuminateResponse)
    {
        $this->setIlluminateResponse($illuminateResponse);
    }

    /**
     * Set the illuminate response.
     *
     * @param  mixed  illuminateResponse
     * @return void
     */
    public function setIlluminateResponse($illuminateResponse)
    {
        if (! $illuminateResponse instanceof SymfonyResponse) {
            $illuminateResponse = new IlluminateResponse((string) $illuminateResponse);
        }

        $this->illuminateResponse = $illuminateResponse;
    }

    /**
     * Send the http response.
     *
     * @param  \Swoole\Http\Response  $swooleResponse
     * @return void
     */
    public function send(SwooleResponse $swooleResponse)
    {
        $this->sendHeaders($swooleResponse);
        $this->sendContent($swooleResponse);

        $this->fireAfterSendingCallbacks();
    }

    /**
     * Send the the headers.
     *
     * @param  \Swoole\Http\Response  $swooleResponse
     * @return void
     */
    protected function sendHeaders(SwooleResponse $swooleResponse)
    {
        $illuminateResponse = $this->illuminateResponse;

        foreach ($illuminateResponse->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            $swooleResponse->header($name, implode('; ', $values));
        }

        $swooleResponse->status($illuminateResponse->getStatusCode());

        foreach ($illuminateResponse->headers->getCookies() as $cookie) {
            $method = $cookie->isRaw() ? 'rawCookie' : 'cookie';

            $swooleResponse->$method(
                $cookie->getName(),
                (string) $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                (string) $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }
    }

    /**
     * Send the http content.
     *
     * @param  \Swoole\Http\Response  $swooleResponse
     * @return void
     */
    protected function sendContent(SwooleResponse $swooleResponse)
    {
        if ($this->illuminateResponse instanceof BinaryFileResponse) {
            $this->sendFile($swooleResponse);
        } else {
            $this->sendChunkedContent($swooleResponse);
        }
    }

    /**
     * Send a binary file response.
     *
     * @param  \Swoole\Http\Response  $swooleResponse
     * @return void
     */
    protected function sendFile(SwooleResponse $swooleResponse)
    {
        $response = new ReflectionObject($this->illuminateResponse);

        $maxlenProperty = $response->getProperty('maxlen');
        $offsetProperty = $response->getProperty('offset');
        $deleteFileAfterSendProperty = $response->getProperty('deleteFileAfterSend');

        $maxlenProperty->setAccessible(true);
        $offsetProperty->setAccessible(true);
        $deleteFileAfterSendProperty->setAccessible(true);

        $length = $maxlenProperty->getValue($this->illuminateResponse);

        if ($length === 0) {
            return;
        }

        if ($length === -1) {
            $length = 0;
        }

        $swooleResponse->sendfile(
            $path = $this->illuminateResponse->getFile()->getPathname(),
            $offsetProperty->getValue($this->illuminateResponse),
            $length
        );

        if ($deleteFileAfterSendProperty->getValue($this->illuminateResponse) && file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Send the chunked content.
     *
     * @param  \Swoole\Http\Response  $swooleResponse
     * @return void
     */
    protected function sendChunkedContent(SwooleResponse $swooleResponse)
    {
        ob_start();

        $this->illuminateResponse->sendContent();

        if (($length = ob_get_length()) <= static::$bufferOutputSize) {
            $swooleResponse->end(ob_get_clean());
        } else {
            foreach ($this->splitContentChunk($length) as $chunk) {
                $swooleResponse->write($chunk);
            }

            $swooleResponse->end();
        }
    }

    /**
     * Split the content chunk.
     *
     * @param  int  $length
     * @return \Generator
     */
    protected function splitContentChunk(int $length)
    {
        $contents = ob_get_clean();

        $chunkSize = static::$bufferOutputSize - 10;

        for ($begin = 0; $begin < $length; $begin += $chunkSize) {
            $chunk = '';

            $end = $begin + $chunkSize;

            if ($end >= $length) {
                $end = $length;
            }

            for ($i = $begin; $i < $end; $i++) {
                $chunk .= $contents[$i];
            }

            yield $chunk;
        }
    }

    /**
     * Register a new after sending callback.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function afterSending(Closure $callback)
    {
        $this->afterSendingCallbacks[] = $callback;
    }

    /**
     * Fire all of the after sending callbacks.
     *
     * @return void
     */
    public function fireAfterSendingCallbacks()
    {
        foreach ($this->afterSendingCallbacks as $callback) {
            $callback();
        }
    }

    /**
     * Get the illuminate response.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIlluminateResponse()
    {
        return $this->illuminateResponse;
    }

    /**
     * Set the buffer output size.
     *
     * @param  int  $size
     * @return void
     */
    public static function setBufferOutputSize(int $size)
    {
        static::$bufferOutputSize = $size;
    }
}
