<?php

namespace HuangYi\Swoole\WebSocket;

use Illuminate\Contracts\Container\Container;

class JoinRoom
{
    /**
     * Container.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Join WebSocket Room.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Handler.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $this->container->make('swoole.websocket')->joinRoom($request);

        return $next($request);
    }
}
