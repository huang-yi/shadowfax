<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use HuangYi\Shadowfax\Contracts\Cleaner;
use HuangYi\Shadowfax\Laravel\CleanersRunner;
use HuangYi\Shadowfax\Tests\Laravel\Cleaners\AfterCleaner;
use HuangYi\Shadowfax\Tests\Laravel\Cleaners\BeforeCleaner;
use HuangYi\Shadowfax\Tests\Laravel\Cleaners\Dir\DirAfterCleaner;
use HuangYi\Shadowfax\Tests\Laravel\Cleaners\Dir\DirBeforeCleaner;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\TestCase;
use stdClass;

class CleanersRunnerTest extends TestCase
{
    public function testAppNamespace()
    {
        $runner = new CleanersRunner([], "App", '');

        $this->assertSame("App\\", $runner->getAppNamespace());
    }


    public function testAppPath()
    {
        $runner = new CleanersRunner([], '', __DIR__.'/..');

        $this->assertSame(realpath(__DIR__.'/..').'/', $runner->getAppPath());
    }


    public function testCleaners()
    {
        $cleaners = [
            __DIR__.'/Cleaners/Dir/',
            BeforeCleaner::class,
            AfterCleaner::class,
        ];

        $namespace = "HuangYi\\Shadowfax\\Tests\\Laravel\\Cleaners\\";

        $appPath = __DIR__.'/Cleaners';

        $runner = new CleanersRunner($cleaners, $namespace, $appPath);

        $this->assertTrue(in_array(BeforeCleaner::class, $runner->getBeforeCleaners()));
        $this->assertTrue(in_array(DirBeforeCleaner::class, $runner->getBeforeCleaners()));
        $this->assertTrue(in_array(AfterCleaner::class, $runner->getAfterCleaners()));
        $this->assertTrue(in_array(DirAfterCleaner::class, $runner->getAfterCleaners()));
    }


    public function testRunBefore()
    {
        $cleaners = [
            InstanceBeforeCleaner::class,
        ];

        $runner = new CleanersRunner($cleaners, '', '');

        $app = new Application();

        $app->instance('shadowfax_before_instance', new stdClass);

        $runner->runBefore($app);

        $this->assertFalse($app->bound('shadowfax_before_instance'));
    }


    public function testRunAfter()
    {
        $cleaners = [
            InstanceAfterCleaner::class,
        ];

        $runner = new CleanersRunner($cleaners, '', '');

        $app = new Application();

        $app->instance('shadowfax_after_instance', new stdClass);

        $runner->runAfter($app);

        $this->assertFalse($app->bound('shadowfax_after_instance'));
    }
}

class InstanceBeforeCleaner implements \HuangYi\Shadowfax\Contracts\BeforeCleaner
{
    public function clean(Container $app)
    {
        unset($app['shadowfax_before_instance']);
    }
}

class InstanceAfterCleaner implements Cleaner
{
    public function clean(Container $app)
    {
        unset($app['shadowfax_after_instance']);
    }
}
