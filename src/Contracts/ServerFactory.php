<?php

namespace HuangYi\Shadowfax\Contracts;

interface ServerFactory
{
    /**
     * Create a server instance.
     *
     * @return \Swoole\Server
     */
    public function create();
}
