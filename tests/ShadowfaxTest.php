<?php

namespace HuangYi\Shadowfax\Tests;

use HuangYi\Shadowfax\Exceptions\EntryNotFoundException;
use HuangYi\Shadowfax\Shadowfax;
use PHPUnit\Framework\TestCase;

class ShadowfaxTest extends TestCase
{
    protected $shadowfax;

    protected $foo;

    public function setUp(): void
    {
        parent::setUp();

        $this->shadowfax = new Shadowfax;

        $this->foo = new \stdClass;

        $this->shadowfax->set('foo', $this->foo);
    }


    public function test_set()
    {
        $this->assertArrayHasKey('foo', $this->shadowfax->getInstances());
        $this->assertEquals($this->foo, $this->shadowfax->getInstances()['foo']);
    }


    public function test_get()
    {
        $this->assertEquals($this->foo, $this->shadowfax->get('foo'));
    }


    public function test_get_nonexistent_entry()
    {
        $this->expectException(EntryNotFoundException::class);

        $this->shadowfax->get('nonexistence');
    }


    public function test_has()
    {
        $this->assertTrue($this->shadowfax->has('foo'));
    }


    public function test_has_not()
    {
        $this->assertFalse($this->shadowfax->has('nonexistence'));
    }
}
