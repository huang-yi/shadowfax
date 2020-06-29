<?php

namespace HuangYi\Shadowfax\Laravel;

use DirectoryIterator;
use HuangYi\Shadowfax\Contracts\Cleaner;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use ReflectionClass;

class CleanersRunner
{
    /**
     * The cleaners list.
     *
     * @var array
     */
    protected $cleaners = [];

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
     * Run the cleaners.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function run(Container $app)
    {
        foreach ($this->cleaners as $cleaner) {
            (new $cleaner)->clean($app);
        }
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
        if (is_subclass_of($cleaner, Cleaner::class) &&
            ! (new ReflectionClass($cleaner))->isAbstract()
        ) {
            $this->cleaners[] = $cleaner;
        }
    }

    /**
     * Get the cleaners list.
     *
     * @return array
     */
    public function getCleaners()
    {
        return $this->cleaners;
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
