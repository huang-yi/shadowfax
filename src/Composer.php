<?php

namespace HuangYi\Shadowfax;

use ReflectionClass;

class Composer
{
    /**
     * The path of "autoload.php"
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
     * The composer controller.
     *
     * @param  string  $path
     * @return void
     */
    public function __construct(string $path)
    {
        $this->path = $path;

        $this->register();
    }

    /**
     * Register autoload.
     *
     * @return void
     */
    protected function register()
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
