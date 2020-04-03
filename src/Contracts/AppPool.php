<?php

namespace HuangYi\Shadowfax\Contracts;

use Illuminate\Contracts\Container\Container;

interface AppPool
{
    /**
     * Pop a Laravel/Lumen application instance from the pool.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function pop(): Container;

    /**
     * Push a Laravel/Lumen application instance to the pool.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function push(Container $app);
}
