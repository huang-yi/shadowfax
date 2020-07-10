<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use HuangYi\Shadowfax\Laravel\ControllersCleaner;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\TestCase;

class ControllersCleanerTest extends TestCase
{
    public function testControllers()
    {
        $controllers = [
            'foo', 'bar',
        ];

        $cleaner = new ControllersCleaner($controllers);

        $this->assertEquals($controllers, $cleaner->getControllers());
        $this->assertFalse($cleaner->getIsCleanAll());
    }


    public function testIsAll()
    {
        $controllers = [
            '*', 'foo', 'bar',
        ];

        $cleaner = new ControllersCleaner($controllers);

        $this->assertNull($cleaner->getControllers());
        $this->assertTrue($cleaner->getIsCleanAll());
    }


    public function testClean()
    {
        $app = new Application();

        $route1 = $app['router']->get('/foo', function () {
            //
        });

        $route2 = $app['router']->get('/bar', function () {
            //
        });

        $route1->controller = new Controller1;
        $route2->controller = new Controller2;

        $cleaner = new ControllersCleaner([Controller1::class]);

        $cleaner->clean($app);

        $this->assertNull($route1->controller);
        $this->assertNotNull($route2->controller);
        $this->assertTrue($app->bound('shadowfax_controller_routes'));
    }


    public function testCleanAll()
    {
        $app = new Application();

        $route1 = $app['router']->get('/foo', function () {
            //
        });

        $route2 = $app['router']->get('/bar', function () {
            //
        });

        $route1->controller = new Controller1;
        $route2->controller = new Controller2;

        $cleaner = new ControllersCleaner(['*']);

        $cleaner->clean($app);

        $this->assertNull($route1->controller);
        $this->assertNull($route2->controller);
        $this->assertFalse($app->bound('shadowfax_controller_routes'));
    }
}

class Controller1
{
}

class Controller2
{
}
