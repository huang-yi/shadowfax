<?php

namespace HuangYi\Shadowfax\Contracts\WebSocket;

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
}
