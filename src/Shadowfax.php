<?php

namespace HuangYi\Shadowfax;

use HuangYi\Shadowfax\Exceptions\EntryNotFoundException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class Shadowfax extends Application implements ContainerInterface
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

        $this->basePath = $basePath;

        static::$instance = $this;

        $this->setDefaultCommand('start');
    }

    /**
     * Register an existing instance in the container.
     *
     * @param  string  $id
     * @param  mixed  $instance
     * @return $this
     */
    public function set($id, $instance)
    {
        $this->instances[$id] = $instance;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if (! $this->has($id)) {
            throw new EntryNotFoundException($id);
        }

        return $this->instances[$id];
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return array_key_exists($id, $this->instances);
    }

    /**
     * Get the base path.
     *
     * @param  string  $path
     * @return string
     */
    public function basePath($path = null)
    {
        return $this->basePath.'/'.ltrim($path, '/');
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
     * Register an existing instance in the container.
     *
     * @param  string  $id
     * @param  mixed  $instance
     * @return void
     */
    public static function instance($id, $instance)
    {
        static::getInstance()->set($id, $instance);
    }

    /**
     * Get entry from the container.
     *
     * @param  string  $id
     * @return mixed
     */
    public static function make($id)
    {
        return static::getInstance()->get($id);
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
}
