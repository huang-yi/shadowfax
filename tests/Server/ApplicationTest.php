<?php

/*
 * This file is part of the huang-yi/laravel-swoole-http package.
 *
 * (c) Huang Yi <coodeer@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HuangYi\Http\Tests\Server;

use HuangYi\Http\Server\Application;
use HuangYi\Http\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApplicationTest extends TestCase
{
    protected $basePath = __DIR__ . '/../fixtures';

    public function testMake()
    {
        $application = $this->makeApplication();

        $this->assertInstanceOf(Application::class, $application);
    }

    public function testMakeInvalidFramework()
    {
        $this->expectException(\Exception::class);

        $this->makeApplication('other');
    }

    public function testRun()
    {
        $application = $this->makeApplication();
        $response = $application->run(Request::create('/'));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('welcome', $response->getContent());
    }

    public function testTerminate()
    {
        $flag = false;

        if (class_exists('\Laravel\Lumen\Application')) {
            $this->assertTrue(true);

            return;
        }

        $application = $this->makeApplication();
        $request = Request::create('/');
        $response = $application->run($request);

        $application->getApplication()->terminating(function () use (&$flag) {
            $flag = true;
        });

        $application->terminate($request, $response);

        $this->assertTrue($flag);
    }

    public function testResetProvider()
    {
        $application = $this->makeApplication();

        $response = $application->run(Request::create('/'));
        $app = $application->getApplication();
        $this->assertSame('bar', $app['singleton.test']->foo);
        $app->singleton('singleton.test', function () {
            $obj = new \stdClass;
            $obj->foo = 'foo';
            return $obj;
        });
        $this->assertSame('foo', $app['singleton.test']->foo);
        $response = $application->resetProviders();
        $this->assertSame('bar', $app['singleton.test']->foo);
    }

    protected function makeApplication($forceFramework = null)
    {
        if (! is_null($forceFramework)) {
            $framework = $forceFramework;
        } elseif (class_exists('\Illuminate\Foundation\Application')) {
            $framework = 'laravel';
        } elseif (class_exists('\Laravel\Lumen\Application')) {
            $framework = 'lumen';
        } else {
            $framework = 'other';
        }

        return Application::make($framework, $this->basePath . '/' . $framework);
    }
}
