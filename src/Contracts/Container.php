<?php

namespace HuangYi\Shadowfax\Contracts;

use Closure;
use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    /**
     * Register a shared binding in the container.
     *
     * @param  string  $abstract
     * @param  \Closure  $concrete
     * @return void
     */
    public function singleton($abstract, Closure $concrete);

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $abstract
     * @param  mixed  $instance
     * @return mixed
     */
    public function instance($abstract, $instance);

    /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @return mixed
     * @throws \HuangYi\Shadowfax\Exceptions\EntryNotFoundException
     */
    public function make($abstract);

    /**
     * Remove a abstract from the container.
     *
     * @param  string  $abstract
     * @return void
     */
    public function forget($abstract);

    /**
     * Flush the container of all instances.
     *
     * @return void
     */
    public function flush();
}
