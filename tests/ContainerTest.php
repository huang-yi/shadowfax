<?php

namespace HuangYi\Shadowfax\Tests;

use HuangYi\Shadowfax\Container;
use HuangYi\Shadowfax\Exceptions\EntryNotFoundException;
use PHPUnit\Framework\TestCase;
use stdClass;

class ContainerTest extends TestCase
{
    protected function tearDown(): void
    {
        Container::setInstance(null);
    }


    public function testContainerSingleton()
    {
        $container = Container::setInstance(new Container);

        $this->assertSame($container, Container::getInstance());
    }


    public function testSingleton()
    {
        $container = new Container;

        $concrete = function () {
            $object = new stdClass;

            $object->name = 'foo';

            return $object;
        };

        $container->singleton('foo', $concrete);

        $foo = $container->make('foo');

        $this->assertArrayHasKey('foo', $container->getBindings());
        $this->assertSame($concrete, $container->getBindings()['foo']);
        $this->assertInstanceOf(stdClass::class, $foo);
        $this->assertSame('foo', $foo->name);
    }


    public function testInstance()
    {
        $container = new Container;

        $foo = new stdClass;

        $container->instance('foo', $foo);

        $this->assertSame($container->make('foo'), $foo);
    }


    public function testMakeNonexistentAbstract()
    {
        $this->expectException(EntryNotFoundException::class);

        $container = new Container;

        $container->make('nonexistent_abstract');
    }
}
