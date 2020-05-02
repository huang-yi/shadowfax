<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use HuangYi\Shadowfax\Laravel\RebindsAbstracts;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use stdClass;

class RebindsAbstractsTest extends TestCase
{
    public function testRebindAbstract()
    {
        $app = new Container;

        $app->singleton('foo', function () {
            return new stdClass;
        });

        $foo1 = $app->make('foo');

        (new RebindsAbstractClass)->rebindAbstract($app, 'foo');

        $foo2 = $app->make('foo');

        $this->assertNotSame($foo1, $foo2);
    }


    public function testRebindAlias()
    {
        $app = new Container;

        $app->singleton('foo', function () {
            return new stdClass;
        });

        $app->alias('foo', 'new foo');

        $foo1 = $app->make('foo');

        (new RebindsAbstractClass)->rebindAbstract($app, 'new foo');

        $foo2 = $app->make('foo');

        $this->assertNotSame($foo1, $foo2);
    }
}

class RebindsAbstractClass
{
    use RebindsAbstracts;
}
