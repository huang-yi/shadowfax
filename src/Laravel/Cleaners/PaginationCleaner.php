<?php

namespace HuangYi\Shadowfax\Laravel\Cleaners;

use HuangYi\Shadowfax\Contracts\BeforeCleaner;
use Illuminate\Contracts\Container\Container;
use Illuminate\Pagination\PaginationServiceProvider;

class PaginationCleaner implements BeforeCleaner
{
    /**
     * Clean something.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function clean(Container $app)
    {
        if (class_exists(PaginationServiceProvider::class)) {
            (new PaginationServiceProvider($app))->register();
        }
    }
}
