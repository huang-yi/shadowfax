<?php

namespace HuangYi\Shadowfax\Http;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Http\Kernel as IlluminateHttpKernel;
use Laravel\Lumen\Application as Lumen;

class Kernel
{
    /**
     * The Laravel/Lumen application.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * Indicates if the kernel is processing a WebSocket request.
     *
     * @var bool
     */
    protected $isWebSocket;

    /**
     * The Swoole Http Kernel.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @param  bool  $isWebSocket
     * @return void
     */
    public function __construct(Container $app, $isWebSocket = false)
    {
        $this->app = $app;
        $this->isWebSocket = $isWebSocket;
    }

    /**
     * Handle the incoming HTTP request.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @return \HuangYi\Shadowfax\Http\Response
     */
    public function handle(Request $request)
    {
        if ($this->isLumen()) {
            return $this->runLumen($request);
        }

        return $this->runLaravel($request);
    }

    /**
     * Run the Laravel framework.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @return \HuangYi\Shadowfax\Http\Response
     */
    public function runLaravel(Request $request)
    {
        $kernel = $this->getKernel();

        $illuminateResponse = $kernel->handle(
            $illuminateRequest = $request->toIlluminate()
        );

        $response = Response::make($illuminateResponse);

        $response->afterSending(function () use (
            $kernel, $illuminateRequest, $illuminateResponse
        ) {
            $kernel->terminate($illuminateRequest, $illuminateResponse);
        });

        return $response;
    }

    /**
     * Run the Lumen framework.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @return \HuangYi\Shadowfax\Http\Response
     */
    public function runLumen(Request $request)
    {
        return Response::make(
            $this->app->handle($request->toIlluminate())
        );
    }

    /**
     * Get the HTTP kernel instance.
     *
     * @return \Illuminate\Contracts\Http\Kernel
     */
    protected function getKernel()
    {
        $kernel = $this->app[IlluminateHttpKernel::class];

        if (! $this->isWebSocket) {
            return $kernel;
        }

        $class = get_class($kernel);

        return new $class($this->app, $this->app['shadowfax.websocket']);
    }

    /**
     * Determine if the framework is Lumen.
     *
     * @return bool
     */
    public function isLumen()
    {
        return $this->app instanceof Lumen;
    }
}
