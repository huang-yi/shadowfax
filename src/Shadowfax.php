<?php

namespace HuangYi\Shadowfax;

use Illuminate\Config\Repository;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Shadowfax extends Container
{
    /**
     * The current version.
     */
    const VERSION = '2.0.0';

    /**
     * The base path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The Symfony console application.
     *
     * @var \Symfony\Component\Console\Application
     */
    protected $console;

    /**
     * The bootstrap classes.
     *
     * @var array
     */
    protected $bootstrappers = [
        \HuangYi\Shadowfax\Bootstrap\CreateCoroutineContainer::class,
        \HuangYi\Shadowfax\Bootstrap\LoadConfiguration::class,
        \HuangYi\Shadowfax\Bootstrap\RegisterEventListeners::class,
    ];

    /**
     *
     * Create a new Shadowfax instance.
     *
     * @param  null  $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->registerBaseBindings();

        $this->bootstrap();

        $this->registerConsoleApplication();
    }

    /**
     * Set the base path.
     *
     * @param  string  $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        return $this;
    }

    /**
     * Get the base path.
     *
     * @param  string  $path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('config', new Repository([]));

        $this->instance('events', new EventDispatcher);
    }

    /**
     * Bootstrap the application.
     *
     * @return void
     */
    protected function bootstrap()
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            (new $bootstrapper)->bootstrap($this);
        }
    }

    /**
     * Register the Symfony console application.
     *
     * @return void
     */
    protected function registerConsoleApplication()
    {
        $this->console = new Application('Shadowfax', static::VERSION);
    }

    /**
     * Get the Symfony console application.
     *
     * @return \Symfony\Component\Console\Application
     */
    public function getConsole()
    {
        return $this->console;
    }
}
