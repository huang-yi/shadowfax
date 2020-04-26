<?php

namespace HuangYi\Shadowfax\Tests\WebSocket;

use HuangYi\Shadowfax\WebSocket\LaravelRoute;
use HuangYi\Shadowfax\WebSocket\LaravelRouter;
use HuangYi\Shadowfax\WebSocket\VerifiesRequest;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Routing\Route;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LaravelRouterTest extends TestCase
{
    public function testListen()
    {
        $router = new LaravelRouter(new Dispatcher);

        $router->listen('/laravel-listen', $handler = new EmptyHandler);

        $route = $router->getRoutes()->match(new IlluminateRequest([], [], [], [], [], ['REQUEST_URI' => '/laravel-listen']));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($handler, $route->getAction('handler'));
    }


    public function testNewRoute()
    {
        $router = new LaravelRouter(new Dispatcher);

        $route = $router->newRoute('GET', '/', ['uses' => function () {
            //
        }]);

        $this->assertInstanceOf(LaravelRoute::class, $route);
    }


    public function testFindRoute()
    {
        $router = new LaravelRouter(new Dispatcher);

        $router->get('/', [
            'handler' => $handler = new EmptyHandler(),
            'middleware' => VerifiesRequest::class,
            'uses' => function () {
                //
            },
        ]);

        $route = $router->findRoute(Request::create('/'));

        $this->assertInstanceOf(LaravelRoute::class, $route);
        $this->assertSame($handler, $route->getAction('handler'));
    }


    public function testFindInvalidRoute()
    {
        $this->expectException(NotFoundHttpException::class);

        $router = new LaravelRouter(new Dispatcher);

        $router->get('/', [
            'uses' => function () {
                //
            },
        ]);

        $router->findRoute(Request::create('/'));
    }
}
