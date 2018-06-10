<?php

namespace HuangYi\Swoole;

use HuangYi\Swoole\Transformers\RequestTransformer;
use HuangYi\Swoole\Transformers\ResponseTransformer;
use Illuminate\Contracts\Http\Kernel;
use Laravel\Lumen\Application as LumenApplication;
use Swoole\Http\Server as SwooleHttpServer;

class HttpServer extends Server
{
    /**
     * The http kernel.
     *
     * @var \Illuminate\Contracts\Http\Kernel
     */
    protected $httpKernel;

    /**
     * Server events.
     *
     * @var array
     */
    protected $events = ['request'];

    /**
     * Define swoole http server class.
     *
     * @return string
     */
    public function swooleServer()
    {
        return SwooleHttpServer::class;
    }

    /**
     * The listener of "workerStart" event.
     *
     * @return void
     */
    public function onWorkerStart()
    {
        parent::onWorkerStart();

        $this->clearCache();

        if (! $this->isLumen()) {
            $this->httpKernel = $this->container->make(Kernel::class);
            $this->httpKernel->bootstrap();
        }
    }

    /**
     * The listener of "request" event.
     *
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @return void
     */
    public function onRequest($request, $response)
    {
        $this->container['events']->fire('swoole.requesting', func_get_args());

        $this->container->instance('swoole.http.request', $request);

        $illuminateRequest = RequestTransformer::make($request)->toIlluminateRequest();

        if ($this->isLumen()) {
            $illuminateResponse = $this->container->handle($illuminateRequest);
        } else {
            $illuminateResponse = $this->httpKernel->handle($illuminateRequest);

            $this->httpKernel->terminate($illuminateRequest, $illuminateResponse);
        }

        ResponseTransformer::make($illuminateResponse)->send($response);

        $this->container['events']->fire('swoole.requested', func_get_args());
    }

    /**
     * Determine whether the framework is Lumen.
     *
     * @return bool
     */
    protected function isLumen()
    {
        return $this->container instanceof LumenApplication;
    }

    /**
     * Get server name
     *
     * @return string
     */
    protected function getServerName()
    {
        return 'swoole-http-server';
    }
}
