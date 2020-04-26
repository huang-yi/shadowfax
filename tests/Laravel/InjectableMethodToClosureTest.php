<?php

namespace HuangYi\Shadowfax\Tests\Laravel;

use Closure;
use HuangYi\Shadowfax\Laravel\InjectableMethodToClosure;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

class InjectableMethodToClosureTest extends TestCase
{
    public function testTransform()
    {
        $object = new InjectableClass;

        $closure = InjectableMethodToClosure::transform($object, 'injectableMethod');

        $this->assertInstanceOf(Closure::class, $closure);

        $params = (new ReflectionFunction($closure))->getParameters();

        $this->assertSame('a', $params[0]->name);
        $this->assertSame(InjectedClass::class, $params[0]->getClass()->name);
        $this->assertSame('b', $params[1]->name);
        $this->assertNull($params[1]->getClass());
        $this->assertSame('c', $params[2]->name);
        $this->assertSame(InjectedClass::class, $params[2]->getClass()->name);
        $this->assertTrue($params[2]->isVariadic());
    }
}

class InjectedClass
{
}

class InjectableClass
{
    public function injectableMethod(InjectedClass $a, $b, InjectedClass ...$c)
    {
        //
    }
}
