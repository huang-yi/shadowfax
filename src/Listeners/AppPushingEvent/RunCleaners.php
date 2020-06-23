<?php

namespace HuangYi\Shadowfax\Listeners\AppPushingEvent;

use DirectoryIterator;
use HuangYi\Shadowfax\Contracts\Cleaner;
use HuangYi\Shadowfax\Events\AppPushingEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;
use Illuminate\Support\Str;
use ReflectionClass;

class RunCleaners
{
    use HasHelpers;

    /**
     * The loaded cleaners.
     *
     * @var array
     */
    protected static $loadedCleaners;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\AppPushingEvent  $event
     * @return void
     */
    public function handle(AppPushingEvent $event)
    {
        static::loadCleaners();

        foreach (static::$loadedCleaners as $cleaner) {
            (new $cleaner)->clean($event->app);
        }
    }

    /**
     * Load the cleaners.
     *
     * @return void
     */
    protected static function loadCleaners()
    {
        if (! is_null(static::$loadedCleaners)) {
            return;
        }

        static::$loadedCleaners = [];

        $cleaners = (array) shadowfax('config')->get('cleaners', []);

        foreach ($cleaners as $cleaner) {
            if (is_dir($cleaner)) {
                static::loadFromDir($cleaner);
            } else {
                static::pushCleaner($cleaner);
            }
        }

        static::$loadedCleaners = array_unique(static::$loadedCleaners);
    }

    /**
     * Load cleaners from directory.
     *
     * @param  string  $dir
     * @return void
     */
    protected static function loadFromDir($dir)
    {
        $dir = rtrim(realpath($dir), '/');
        $namespace = app()->getNamespace();
        $appPath = realpath(app()->path()).'/';

        foreach (new DirectoryIterator('glob://'.$dir.'/*.php') as $file) {
            $cleaner = $namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($file->getPathname(), $appPath)
            );

            static::pushCleaner($cleaner);
        }
    }

    /**
     * Push the cleaner class to cache array.
     *
     * @param  string  $cleaner
     * @return void
     */
    protected static function pushCleaner($cleaner)
    {
        if (is_subclass_of($cleaner, Cleaner::class) &&
            ! (new ReflectionClass($cleaner))->isAbstract()) {
            static::$loadedCleaners[] = $cleaner;
        }
    }
}
