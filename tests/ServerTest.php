<?php

namespace HuangYi\Shadowfax\Tests;

use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Http\Client;

class ServerTest extends TestCase
{
    public function test_laravel()
    {
        $client = new Client('127.0.0.1', 1225);

        $client->get('/');

        $this->assertEquals('Hello Shadowfax! (Laravel)', $client->body);
    }


    public function test_lumen()
    {
        $client = new Client('127.0.0.1', 1235);

        $client->get('/');

        $this->assertEquals('Hello Shadowfax! (Lumen)', $client->body);
    }


    public function test_coroutine_context()
    {
        $client1 = new Client('127.0.0.1', 1225);
        $client1->setDefer();
        $client1->get('/blocking?input=1');

        $client2 = new Client('127.0.0.1', 1225);
        $client2->setDefer();
        $client2->get('/blocking?input=2');

        $client3 = new Client('127.0.0.1', 1225);
        $client3->setDefer();
        $client3->get('/blocking?input=3');

        $client1->recv();
        $this->assertEquals('1->1', $client1->body);

        $client2->recv();
        $this->assertEquals('2->2', $client2->body);

        $client3->recv();
        $this->assertEquals('3->3', $client3->body);
    }
}
