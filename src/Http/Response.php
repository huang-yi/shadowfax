<?php

namespace HuangYi\Shadowfax\Http;

use Closure;
use Illuminate\Http\Response as IlluminateResponse;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response
{
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
            $content = (string) $illuminateResponse;
            $illuminateResponse = new IlluminateResponse($content);
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

        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (! $illuminateResponse->headers->has('Date')) {
            $illuminateResponse->setDate(\DateTime::createFromFormat('U', time()));
        }

        // Set headers.
        foreach ($illuminateResponse->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            $swooleResponse->header($name, implode('; ', $values));
        }

        // Set status code.
        $swooleResponse->status($illuminateResponse->getStatusCode());

        // Set cookies.
        foreach ($illuminateResponse->headers->getCookies() as $cookie) {
            $method = $cookie->isRaw() ? 'rawCookie' : 'cookie';

            $swooleResponse->$method(
                $cookie->getName(), $cookie->getValue(),
                $cookie->getExpiresTime(), $cookie->getPath(),
                $cookie->getDomain(), $cookie->isSecure(),
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
        $swooleResponse->end($this->illuminateResponse->getContent());
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
}
