<?php

namespace HuangYi\Shadowfax\Tests\Laravel\Cleaners;

use HuangYi\Shadowfax\Contracts\BeforeCleaner as BeforeCleanerContract;
use Illuminate\Contracts\Container\Container;

class BeforeCleaner implements BeforeCleanerContract
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
