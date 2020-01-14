<?php

namespace HuangYi\Shadowfax;

use HuangYi\Shadowfax\Exceptions\InstanceNotFoundException;
use Symfony\Component\Console\Application;

class Shadowfax extends Application
{
    /**
     * The current version.
     */
    const VERSION = '1.0.0';

    /**
     * The Shadowfax instance.
     *
     * @var static
     */
    protected static $instance;

    /**
     * The registered instances.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The base path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The Shadowfax constructor.
     *
     * @param  string  $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        parent::__construct('Shadowfax', static::VERSION);

        $this->basePath = rtrim($basePath, '/');

        static::setInstance($this);

        $this->setDefaultCommand('start');
    }

    /**
     * Register an existing instance in the container.
     *
     * @param  string  $abstract
     * @param  mixed  $instance
     * @return $this
     */
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;

        return $this;
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @return mixed
     */
    public function make($abstract)
    {
        if (! $this->hasInstance($abstract)) {
            throw new InstanceNotFoundException($abstract);
        }

        return $this->instances[$abstract];
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function hasInstance($abstract)
    {
        return isset($this->instances[$abstract]);
    }

    /**
     * Get the base path.
     *
     * @param  string  $path
     * @return string
     */
    public function basePath($path = null)
    {
        if (! is_null($path) && $path[0] == '/') {
            return $path;
        }

        return $path ? $this->basePath.'/'.$path : $this->basePath;
    }

    /**
     * Get the instances.
     *
     * @return array
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     * Get the instance.
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
     * Set the global instance.
     *
     * @param  static  $instance
     * @return void
     */
    public static function setInstance($instance)
    {
        static::$instance = $instance;
    }
}
