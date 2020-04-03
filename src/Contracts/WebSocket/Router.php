<?php

namespace HuangYi\Shadowfax\Contracts\WebSocket;

use HuangYi\Shadowfax\Http\Request;

interface Router
{
    /**
     * Listen a uri.
     *
     * @param  string  $uri
     * @param  Handler  $handler
     * @return mixed
     */
    public function listen(string $uri, Handler $handler);

    /**
     * Find the handler.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @return \HuangYi\Shadowfax\Contracts\WebSocket\Handler
     */
    public function findHandler(Request $request): Handler;
}
