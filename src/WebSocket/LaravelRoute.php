<?php

namespace HuangYi\Shadowfax\WebSocket;

use Illuminate\Container\Container;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Route;

class LaravelRoute extends Route
{
    /**
     * Run the route action and return the response.
     *
     * @return mixed
     */
    public function run()
    {
        $this->container = $this->container ?: new Container;

        try {
            $handler = $this->getAction('handler');

            if (method_exists($handler, 'onHandshake')) {
                return $this->controllerDispatcher()->dispatch($this, $handler, 'onHandshake');
            }
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }
}
