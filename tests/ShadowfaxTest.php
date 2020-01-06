<?php

namespace HuangYi\Shadowfax\Tests;

use HuangYi\Shadowfax\Exceptions\InstanceNotFoundException;
use HuangYi\Shadowfax\Shadowfax;
use PHPUnit\Framework\TestCase;

class ShadowfaxTest extends TestCase
{
    protected $shadowfax;

    public function setUp(): void
    {
        parent::setUp();

        $this->shadowfax = new Shadowfax;
    }


    public function test_instance()
    {
        $foo = new \stdClass;
        $this->shadowfax->instance('foo', $foo);

        $this->assertArrayHasKey('foo', $this->shadowfax->getInstances());
        $this->assertEquals($foo, $this->shadowfax->getInstances()['foo']);
    }


    public function test_make()
    {
        $foo = new \stdClass;
        $this->shadowfax->instance('foo', $foo);

        $this->assertEquals($foo, $this->shadowfax->make('foo'));
    }


    public function test_make_nonexistent_instance()
    {
        $this->expectException(InstanceNotFoundException::class);

        $this->shadowfax->make('nonexistence');
    }


    public function test_has_instance()
    {
        $foo = new \stdClass;
        $this->shadowfax->instance('foo', $foo);

        $this->assertTrue($this->shadowfax->hasInstance('foo'));
    }


    public function test_has_not_instance()
    {
        $this->assertFalse($this->shadowfax->hasInstance('nonexistence'));
    }
}
