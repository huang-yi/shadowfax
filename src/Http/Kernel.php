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
     * The Swoole Http Kernel.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Handle the incoming HTTP request.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @param  bool $isWebSocket
     * @return \HuangYi\Shadowfax\Http\Response
     */
    public function handle(Request $request, bool $isWebSocket = false)
    {
        if ($this->isLumen()) {
            return $this->runLumen($request, $isWebSocket);
        }

        return $this->runLaravel($request, $isWebSocket);
    }

    /**
     * Run the Laravel framework.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @param  bool  $isWebSocket
     * @return \HuangYi\Shadowfax\Http\Response
     */
    public function runLaravel(Request $request, $isWebSocket = false)
    {
        $kernel = $this->getKernel($isWebSocket);

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
     * Get the HTTP kernel instance.
     *
     * @param  bool  $isWebSocket
     * @return \Illuminate\Contracts\Http\Kernel
     */
    protected function getKernel($isWebSocket = false)
    {
        $kernel = $this->app[IlluminateHttpKernel::class];

        if (! $isWebSocket) {
            return $kernel;
        }

        $class = get_class($kernel);

        return new $class($this->app, $this->app['shadowfax.websocket']);
    }

    /**
     * Run the Lumen framework.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @param  bool  $isWebSocket
     * @return \HuangYi\Shadowfax\Http\Response
     */
    public function runLumen(Request $request, $isWebSocket = false)
    {
        if ($isWebSocket) {
            $router = $this->app->router;
            $this->app->router = $this->app['shadowfax.websocket'];
        }

        try {
            return Response::make(
                $this->app->handle($request->toIlluminate())
            );
        } finally {
            if ($isWebSocket) {
                $this->app->router = $router;
            }
        }
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
