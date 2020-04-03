<?php

namespace HuangYi\Shadowfax;

use ArrayAccess;
use HuangYi\Shadowfax\Contracts\Container as ContainerContract;
use HuangYi\Shadowfax\Exceptions\EntryNotFoundException;

class Container implements ArrayAccess, ContainerContract
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * The container's shared instances.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $abstract
     * @param  mixed  $instance
     * @return mixed
     */
    public function instance($abstract, $instance)
    {
        return $this->instances[$abstract] = $instance;
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @return mixed
     */
    public function make($abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        throw new EntryNotFoundException($abstract);
    }

    /**
     * Remove a abstract from the container.
     *
     * @param  string  $abstract
     * @return void
     */
    public function forget($abstract)
    {
        unset($this->instances[$abstract]);
    }

    /**
     * Flush the container of all instances.
     *
     * @return void
     */
    public function flush()
    {
        $this->instances = [];
    }

    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param  \HuangYi\Shadowfax\Contracts\Container|null  $container
     * @return \HuangYi\Shadowfax\Contracts\Container|static
     */
    public static function setInstance(ContainerContract $container = null)
    {
        return static::$instance = $container;
    }

    /**
     *  {@inheritDoc}
     */
    public function get($id)
    {
        return $this->make($id);
    }

    /**
     *  {@inheritDoc}
     */
    public function has($id)
    {
        return isset($this->instances[$id]);
    }

    /**
     * Determine if a given offset exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get the value at a given offset.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->make($key);
    }

    /**
     * Set the value at a given offset.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->instance($key, $value);
    }

    /**
     * Unset the value at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->forget($key);
    }
}
