<?php

namespace HuangYi\Swoole\Websocket\Middleware;

use Illuminate\Contracts\Container\Container;

class JoinNamespace
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Join namespace.
     *
     * @param Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $path = $request->path();
        $socketId = $this->container['swoole.http.request']->fd;

        $this->container['swoole.websocket.namespace']->join($path, $socketId);

        return $next($request);
    }
}
