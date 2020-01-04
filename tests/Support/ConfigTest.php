<?php

namespace HuangYi\Shadowfax\Tests\Support;

use HuangYi\Shadowfax\Support\Config;
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
        $config = new Config(__DIR__.'/user.ini');

        $this->assertEquals('user-shadowfax', $config->get('name'));
    }
}
