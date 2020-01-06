<?php

namespace HuangYi\Shadowfax\Tests;

use HuangYi\Shadowfax\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function test_get()
    {
        $config = new Config;

        $this->assertEquals('shadowfax', $config->get('name'));
    }


    public function test_get_default()
    {
        $config = new Config;

        $this->assertEquals('foo', $config->get('nonexistence', 'foo'));
    }


    public function test_get_user_config()
    {
        $config = new Config(__DIR__.'/shadowfax.ini');

        $this->assertEquals('test-shadowfax', $config->get('name'));
    }
}
