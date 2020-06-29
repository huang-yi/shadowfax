<?php

namespace HuangYi\Shadowfax\Tests\Laravel\Cleaners\Dir;

use HuangYi\Shadowfax\Contracts\Cleaner;
use Illuminate\Contracts\Container\Container;

class FooCleaner implements Cleaner
{
    /**
     * Clean something.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function clean(Container $app)
    {
        //
    }
}
