<?php

namespace HuangYi\Shadowfax\Laravel;

use DirectoryIterator;
use HuangYi\Shadowfax\Contracts\BeforeCleaner;
use HuangYi\Shadowfax\Contracts\Cleaner;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use ReflectionClass;

class CleanersRunner
{
    /**
     * The before cleaners.
     *
     * @var array
     */
    protected $beforeCleaners = [];

    /**
     * The after cleaners.
     *
     * @var array
     */
    protected $afterCleaners = [];

    /**
     * The app namespace.
     *
     * @var string
     */
    protected $appNamespace;

    /**
     * The app path.
     *
     * @var string
     */
    protected $appPath;

    /**
     * Create a new CleanersRunner instance.
     *
     * @param  array  $cleaners
     * @param  string  $appNamespace
     * @param  string  $appPath
     * @return void
     */
    public function __construct(array $cleaners, $appNamespace, $appPath)
    {
        $this->appNamespace = rtrim($appNamespace, "\\")."\\";
        $this->appPath = rtrim(realpath($appPath), '/').'/';

        $this->initialize($cleaners);
    }

    /**
     * Run the before cleaners.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function runBefore(Container $app)
    {
        foreach ($this->beforeCleaners as $cleaner) {
            (new $cleaner)->clean($app);
        }
    }

    /**
     * Run the after cleaners.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function runAfter(Container $app)
    {
        foreach ($this->afterCleaners as $cleaner) {
            (new $cleaner)->clean($app);
        }
    }

    /**
     * Run the cleaners.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     *
     * @deprecated
     */
    public function run(Container $app)
    {
        $this->runAfter($app);
    }

    /**
     * Initialize the runner.
     *
     * @param  array  $cleaners
     * @return void
     */
    protected function initialize(array $cleaners)
    {
        foreach (array_unique($cleaners) as $cleaner) {
            if (is_dir($cleaner)) {
                $this->loadFromDir($cleaner);
            } else {
                $this->pushCleaner($cleaner);
            }
        }

        $this->beforeCleaners = array_unique($this->beforeCleaners);
        $this->afterCleaners = array_unique($this->afterCleaners);
    }

    /**
     * Load cleaners from directory.
     *
     * @param  string  $dir
     * @return void
     */
    protected function loadFromDir($dir)
    {
        $dir = rtrim(realpath($dir), '/');

        foreach (new DirectoryIterator('glob://'.$dir.'/*.php') as $file) {
            $cleaner = $this->appNamespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($file->getPathname(), $this->appPath)
            );

            $this->pushCleaner($cleaner);
        }
    }

    /**
     * Push the cleaner class to cache array.
     *
     * @param  string  $cleaner
     * @return void
     */
    protected function pushCleaner($cleaner)
    {
        if (! is_subclass_of($cleaner, Cleaner::class)) {
            return;
        }

        if ((new ReflectionClass($cleaner))->isAbstract()) {
            return;
        }

        if (is_subclass_of($cleaner, BeforeCleaner::class)) {
            $this->beforeCleaners[] = $cleaner;
        } else {
            $this->afterCleaners[] = $cleaner;
        }
    }

    /**
     * Get the before cleaners.
     *
     * @return array
     */
    public function getBeforeCleaners()
    {
        return $this->beforeCleaners;
    }

    /**
     * Get the after cleaners.
     *
     * @return array
     */
    public function getAfterCleaners()
    {
        return $this->afterCleaners;
    }

    /**
     * Get the after cleaners.
     *
     * @return array
     *
     * @deprecated
     */
    public function getCleaners()
    {
        return $this->getAfterCleaners();
    }

    /**
     * Get the app namespace.
     *
     * @return string
     */
    public function getAppNamespace()
    {
        return $this->appNamespace;
    }

    /**
     * Get the app path.
     *
     * @return string
     */
    public function getAppPath()
    {
        return $this->appPath;
    }
}
