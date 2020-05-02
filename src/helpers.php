<?php

use HuangYi\Shadowfax\Shadowfax;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Swoole\Coroutine;

if (! function_exists('shadowfax')) {
    /**
     * Get the Shadowfax instance.
     *
     * @param  string|null  $abstract
     * @return mixed|\HuangYi\Shadowfax\Shadowfax
     */
    function shadowfax($abstract = null)
    {
        if (is_null($abstract)) {
            return Shadowfax::getInstance();
        }

        return Shadowfax::getInstance()->make($abstract);
    }
}

if (! function_exists('shadowfax_correct_container')) {
    /**
     * Correct the global Laravel IoC container in coroutine.
     *
     * @param  \Illuminate\Contracts\Container\Container  $current
     * @return void
     */
    function shadowfax_correct_container(ContainerContract $current)
    {
        $container = shadowfax_get_coroutine_container();

        if ($container && $current !== $container) {
            shadowfax_set_global_container($container);
        }
    }
}

if (! function_exists('shadowfax_get_coroutine_container')) {
    /**
     * Get the Laravel IoC container from a coroutine.
     *
     * @param  int  $cid
     * @return \Illuminate\Contracts\Container\Container|null
     */
    function shadowfax_get_coroutine_container($cid = null)
    {
        if (in_array($cid, [-1, false], true)) {
            return null;
        }

        // We will use the container set in the parent coroutine
        // when the current coroutine is not set a global container.
        if (! $app = Coroutine::getContext($cid)->laravel ?? null) {
            return shadowfax_get_coroutine_container(Coroutine::getPcid($cid));
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
        Coroutine::getContext()->laravel = $container;

        Container::setInstance($container);
        Facade::setFacadeApplication($container);
        Facade::clearResolvedInstances();

        if ($container->bound('db')) {
            Model::setConnectionResolver($container['db']);
        }
    }
}
