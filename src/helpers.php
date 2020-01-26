<?php

use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

if (! function_exists('shadowfax_correct_container')) {
    /**
     * Correct the IoC container.
     *
     * @param  \Illuminate\Contracts\Container\Container  $current
     * @return void
     */
    function shadowfax_correct_container(ContainerContract $current)
    {
        $instance = shadowfax_get_coroutine_container();

        if ($instance && $current !== $instance) {
            shadowfax_set_global_container($instance);
        }
    }
}

if (! function_exists('shadowfax_get_coroutine_container')) {
    /**
     * Get the IoC container in coroutine environment.
     *
     * @param  int  $cid
     * @return \Illuminate\Contracts\Container\Container|null
     */
    function shadowfax_get_coroutine_container($cid = null)
    {
        if (in_array($cid, [-1, false], true)) {
            return null;
        }

        if (! $app = Swoole\Coroutine::getContext($cid)->laravel ?? null) {
            return shadowfax_get_coroutine_container(Swoole\Coroutine::getPcid($cid));
        }

        return $app;
    }
}

if (! function_exists('shadowfax_set_global_container')) {
    /**
     * Set the global IoC container.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    function shadowfax_set_global_container(ContainerContract $container)
    {
        Container::setInstance($container);
        Facade::setFacadeApplication($container);
        Facade::clearResolvedInstances();

        if ($container->bound('db')) {
            Model::setConnectionResolver($container['db']);
        }
    }
}
