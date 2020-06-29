<?php

namespace HuangYi\Shadowfax\Laravel;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;

class ControllersCleaner
{
    /**
     * The controllers list.
     *
     * @var array
     */
    protected $controllers;

    /**
     * Indicates whether to clear all controller instances.
     *
     * @var bool
     */
    protected $isAll;

    /**
     * Create a new ControllersCleaner instance.
     *
     * @param  array  $controllers
     * @return void
     */
    public function __construct(array $controllers)
    {
        $this->initialize($controllers);
    }

    /**
     * Initialize the instance.
     *
     * @param  array  $controllers
     * @return void
     */
    protected function initialize(array $controllers)
    {
        if (! $this->isAll = in_array('*', $controllers, true)) {
            $this->controllers = array_unique($controllers);
        }
    }

    /**
     * Clean the controllers.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function clean(Container $app)
    {
        if ($this->isAll) {
            $this->cleanAll($app);
        } else {
            $this->cacheRoutes($app);

            foreach ($app['shadowfax_controller_routes'] as $route) {
                $route->controller = null;
            }
        }
    }

    /**
     * Clean all controllers.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    protected function cleanAll(Container $app)
    {
        foreach ($app['router']->getRoutes() as $route) {
            $route->controller = null;
        }
    }

    /**
     * Cache the controller routes.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    protected function cacheRoutes(Container $app)
    {
        if ($app->bound('shadowfax_controller_routes')) {
            return;
        }

        $routes = Collection::make();

        foreach ($app['router']->getRoutes() as $route) {
            if (! $route->controller) {
                continue;
            }

            if (in_array(get_class($route->controller), $this->controllers, true)) {
                $routes->push($route);
            }
        }

        $app->instance('shadowfax_controller_routes', $routes);
    }

    /**
     * Get the controllers.
     *
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * Get the all flag.
     *
     * @return bool
     */
    public function getIsAll()
    {
        return $this->isAll;
    }
}
