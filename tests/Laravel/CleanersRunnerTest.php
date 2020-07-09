<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use HuangYi\Shadowfax\Contracts\Cleaner;
use HuangYi\Shadowfax\Laravel\CleanersRunner;
use HuangYi\Shadowfax\Tests\Laravel\Cleaners\Dir\BarCleaner;
use HuangYi\Shadowfax\Tests\Laravel\Cleaners\Dir\FooCleaner;
use HuangYi\Shadowfax\Tests\Laravel\Cleaners\DummyCleaner;
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
            DummyCleaner::class,
        ];

        $namespace = "HuangYi\\Shadowfax\\Tests\\Laravel\\Cleaners\\";

        $appPath = __DIR__.'/Cleaners';

        $runner = new CleanersRunner($cleaners, $namespace, $appPath);

        $this->assertTrue(in_array(BarCleaner::class, $runner->getCleaners()));
        $this->assertTrue(in_array(FooCleaner::class, $runner->getCleaners()));
        $this->assertTrue(in_array(DummyCleaner::class, $runner->getCleaners()));
    }


    public function testRun()
    {
        $cleaners = [
            InstanceCleaner::class,
        ];

        $runner = new CleanersRunner($cleaners, '', '');

        $app = new Application();

        $app->instance('shadowfax_test_instance', new stdClass);

        $runner->run($app);

        $this->assertFalse($app->bound('shadowfax_test_instance'));
    }
}

class InstanceCleaner implements Cleaner
{
    public function clean(Container $app)
    {
        unset($app['shadowfax_test_instance']);
    }
}
