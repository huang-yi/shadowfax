<?php

namespace HuangYi\Http\Transformers;

use Illuminate\Http\Request as IlluminateRequest;
use Swoole\Http\Request as SwooleRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class RequestTransformer
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $illuminateRequest;

    /**
     * @var \Swoole\Http\Request
     */
    protected $swooleRequest;

    /**
     * Make a request transformer.
     *
     * @param \Swoole\Http\Request $swooleRequest
     * @return static
     */
    public static function make(SwooleRequest $swooleRequest)
    {
        return new static($swooleRequest);
    }

    /**
     * RequestTransformer.
     *
     * @param \Swoole\Http\Request $swooleRequest
     */
    public function __construct(SwooleRequest $swooleRequest)
    {
        $this->swooleRequest = $swooleRequest;
    }

    /**
     * Transform to illuminate request.
     *
     * @return \Illuminate\Http\Request
     */
    public function toIlluminateRequest()
    {
        list($get, $post, $cookie, $files, $server, $content)
            = $this->transformIlluminateParameters($this->swooleRequest);

        return $this->createIlluminateRequest(
            $get, $post, $cookie, $files, $server, $content
        );
    }

    /**
     * Create Illuminate Request.
     *
     * @param array $get
     * @param array $post
     * @param array $cookie
     * @param array $files
     * @param array $server
     * @param string $content
     * @return \Illuminate\Http\Request
     */
    protected function createIlluminateRequest($get, $post, $cookie, $files, $server, $content = null)
    {
        IlluminateRequest::enableHttpMethodParameterOverride();

        $request = new SymfonyRequest($get, $post, [], $cookie, $files, $server, $content);

        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($content, $data);
            $request->request = new ParameterBag($data);
        }

        return IlluminateRequest::createFromBase($request);
    }

    /**
     * Transforms request parameters.
     *
     * @param \Swoole\Http\Request $request
     * @return array
     */
    protected function transformIlluminateParameters(SwooleRequest $request)
    {
        $get = isset($request->get) ? $request->get : [];
        $post = isset($request->post) ? $request->post : [];
        $cookie = isset($request->cookie) ? $request->cookie : [];
        $files = isset($request->files) ? $request->files : [];
        $header = isset($request->header) ? $request->header : [];
        $server = isset($request->server) ? $request->server : [];
        $server = $this->transformServerParameters($server, $header);
        $content = $request->rawContent();

        return [$get, $post, $cookie, $files, $server, $content];
    }

    /**
     * Transforms $_SERVER array.
     *
     * @param array $server
     * @param array $header
     * @return array
     */
    protected function transformServerParameters(array $server, array $header)
    {
        $__SERVER = [];

        foreach ($server as $key => $value) {
            $key = strtoupper($key);
            $__SERVER[$key] = $value;
        }

        foreach ($header as $key => $value) {
            $key = strtoupper(str_replace('-', '_', $key));
            $__SERVER[$key] = $value;
        }

        return $__SERVER;
    }
}
