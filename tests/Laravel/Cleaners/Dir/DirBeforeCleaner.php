<?php

namespace HuangYi\Shadowfax\Tests\Laravel\Cleaners\Dir;

use HuangYi\Shadowfax\Contracts\BeforeCleaner as CleanerContract;
use Illuminate\Contracts\Container\Container;

class DirBeforeCleaner implements CleanerContract
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
