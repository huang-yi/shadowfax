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
     * Determine if the framework is Lumen.
     *
     * @var bool
     */
    protected $isLumen;

    /**
     * The Swoole Http Kernel.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->isLumen = $this->isLumen($app);
    }

    /**
     * Handle the incoming HTTP request.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @return \HuangYi\Shadowfax\Http\Response
     */
    public function handle(Request $request)
    {
        if ($this->isLumen) {
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
        $kernel = $this->app->make(IlluminateHttpKernel::class);

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
     * Determine if the framework is Lumen.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return bool
     */
    public function isLumen(Container $app)
    {
        return $app instanceof Lumen;
    }
}
