<?php

namespace HuangYi\Shadowfax\Contracts;

use Illuminate\Contracts\Container\Container;

interface Cleaner
{
    /**
     * Clean something.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function clean(Container $app);
}
