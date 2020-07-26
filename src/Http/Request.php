<?php

namespace HuangYi\Shadowfax\Http;

use Illuminate\Http\Request as IlluminateRequest;
use Swoole\Http\Request as SwooleRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request
{
    /**
     * The Swoole http request.
     *
     * @var \Swoole\Http\Request
     */
    protected $swooleRequest;

    /**
     * The Illuminate http request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $illuminateRequest;

    /**
     * Make a new Request.
     *
     * @param  \Swoole\Http\Request  $swooleRequest
     * @return static
     */
    public static function make(SwooleRequest $swooleRequest)
    {
        return new static($swooleRequest);
    }

    /**
     * Create a new Request.
     *
     * @param  \Swoole\Http\Request  $swooleRequest
     * @return void
     */
    public function __construct(SwooleRequest $swooleRequest)
    {
        $this->swooleRequest = $swooleRequest;
    }

    /**
     * Converts to Illuminate http request.
     *
     * @return \Illuminate\Http\Request
     */
    public function toIlluminate()
    {
        if ($this->illuminateRequest) {
            return $this->illuminateRequest;
        }

        IlluminateRequest::enableHttpMethodParameterOverride();

        $symfonyRequest = new SymfonyRequest(
            $this->swooleRequest->get ?? [],
            $this->swooleRequest->post ?? [],
            ['swoole_request' => $this->swooleRequest],
            $this->swooleRequest->cookie ?? [],
            $this->swooleRequest->files ?? [],
            $this->formatServerVars(),
            $this->swooleRequest->rawContent()
        );

        return $this->illuminateRequest = IlluminateRequest::createFromBase($symfonyRequest);
    }

    /**
     * Formats the $_SERVER variables.
     *
     * @return array
     */
    protected function formatServerVars()
    {
        $formatted = [];

        $server = $this->swooleRequest->server ?? [];
        $header = $this->swooleRequest->header ?? [];

        foreach ($server as $key => $value) {
            $key = strtoupper($key);

            $formatted[$key] = $value;
        }

        foreach ($header as $key => $value) {
            $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));

            $formatted[$key] = $value;
        }

        return $formatted;
    }

    /**
     * Get the Illuminate http request.
     *
     * @return \Illuminate\Http\Request
     */
    public function getIlluminateRequest()
    {
        return $this->toIlluminate();
    }

    /**
     * Get the Swoole http request.
     *
     * @return \Swoole\Http\Request
     */
    public function getSwooleRequest()
    {
        return $this->swooleRequest;
    }
}
