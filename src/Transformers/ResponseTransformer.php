<?php

namespace HuangYi\Swoole\Transformers;

use Illuminate\Http\Response as IlluminateResponse;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseTransformer
{
    /**
     * @var \Illuminate\Http\Response
     */
    protected $illuminateResponse;

    /**
     * Make a ResponseTransformer.
     *
     * @param mixed $illuminateResponse
     * @return static
     */
    public static function make($illuminateResponse)
    {
        return new static($illuminateResponse);
    }

    /**
     * ResponseTransformer.
     *
     * @param mixed $illuminateResponse
     * @return void
     */
    public function __construct($illuminateResponse)
    {
        $this->prepareIlluminateResponse($illuminateResponse);
    }

    /**
     * Send http response.
     *
     * @param \Swoole\Http\Response $swooleResponse
     * @return void
     */
    public function send(SwooleResponse $swooleResponse)
    {
        $this->sendHeaders($swooleResponse);
        $this->sendContent($swooleResponse);
    }

    /**
     * Send HTTP headers.
     *
     * @param \Swoole\Http\Response $swooleResponse
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
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }

        // Set status code.
        $swooleResponse->status($illuminateResponse->getStatusCode());

        // Set cookies.
        foreach ($illuminateResponse->headers->getCookies() as $cookie) {
            $method = $cookie->isRaw() ? 'rawcookie' : 'cookie';

            $swooleResponse->$method(
                $cookie->getName(), $cookie->getValue(),
                $cookie->getExpiresTime(), $cookie->getPath(),
                $cookie->getDomain(), $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }
    }

    /**
     * Send HTTP content.
     *
     * @param \Swoole\Http\Response $swooleResponse
     * @return void
     */
    protected function sendContent(SwooleResponse $swooleResponse)
    {
        ob_start();

        $this->illuminateResponse->sendContent();

        $swooleResponse->end(ob_get_contents());

        ob_end_clean();
    }

    /**
     * Prepare illuminate response.
     *
     * @param mixed illuminateResponse
     * @return void
     */
    protected function prepareIlluminateResponse($illuminateResponse)
    {
        if (! $illuminateResponse instanceof SymfonyResponse) {
            $content = (string) $illuminateResponse;
            $illuminateResponse = new IlluminateResponse($content);
        }

        $this->illuminateResponse = $illuminateResponse;
    }
}
