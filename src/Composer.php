<?php

namespace HuangYi\Shadowfax;

use ReflectionClass;

class Composer
{
    /**
     * The path of autoload.
     *
     * @var string
     */
    protected $path;

    /**
     * The composer class loader.
     *
     * @var \Composer\Autoload\ClassLoader
     */
    protected $loader;

    /**
     * The autoload real class.
     *
     * @var string
     */
    protected $real;

    /**
     * Composer construct.
     *
     * @param  string  $path
     * @return void
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Register autoload.
     *
     * @return void
     */
    public function register()
    {
        $this->loader = require $this->path;

        $this->real = $this->matchRealClass();
    }

    /**
     * Match the real class.
     *
     * @return string
     */
    protected function matchRealClass()
    {
        preg_match('{ComposerAutoloaderInit([^:\s]+)::}', file_get_contents($this->path), $match);

        return 'ComposerAutoloaderInit'.$match[1];
    }

    /**
     * Unregister autoload.
     *
     * @return void
     */
    public function unregister()
    {
        $this->loader->unregister();
    }

    /**
     * Reload autoload.
     *
     * @return void
     */
    public function reload()
    {
        if ($this->real == $this->matchRealClass()) {
            $property = (new ReflectionClass($this->real))->getProperty('loader');

            $property->setAccessible(true);
            $property->setValue(null);
        }

        $this->register();
    }
}
