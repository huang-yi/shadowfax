<?php

namespace HuangYi\Shadowfax\Contracts;

use Illuminate\Contracts\Container\Container;

interface AppFactory
{
    /**
     * Make a Laravel/Lumen application.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function make(): Container;

    /**
     * Recycle the Laravel/Lumen application.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function recycle(Container $app);
}
