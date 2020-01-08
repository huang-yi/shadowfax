<?php

use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

if (! function_exists('shadowfax_correct_app')) {
    /**
     * Correct the Laravel Application.
     *
     * @param  \Illuminate\Contracts\Container\Container  $current
     * @return void
     */
    function shadowfax_correct_app(ContainerContract $current)
    {
        $instance = shadowfax_get_coroutine_app();

        if ($instance && $current !== $instance) {
            Container::setInstance($instance);
            Facade::setFacadeApplication($instance);
            Facade::clearResolvedInstances();
        }
    }
}

if (! function_exists('shadowfax_get_coroutine_app')) {
    /**
     * Get the Laravel application in coroutine environment.
     *
     * @param  int  $cid
     * @return \Illuminate\Contracts\Container\Container|null
     */
    function shadowfax_get_coroutine_app($cid = null)
    {
        if (in_array($cid, [-1, false], true)) {
            return null;
        }

        if (! $app = Swoole\Coroutine::getContext($cid)->laravel ?? null) {
            return shadowfax_get_coroutine_app(Swoole\Coroutine::getPcid($cid));
        }

        return $app;
    }
}
