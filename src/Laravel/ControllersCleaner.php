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
    protected $isCleanAll;

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
        if (! $this->isCleanAll = in_array('*', $controllers, true)) {
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
        if ($this->isCleanAll) {
            $this->cleanAll($app);
        } else {
            $this->cleanPartial($app);
        }
    }

    /**
     * Clean all the controller instances.
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
     * Clean the configured controllers.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    protected function cleanPartial(Container $app)
    {
        $this->cacheRoutes($app);

        foreach ($app['shadowfax_controller_routes'] as $route) {
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
     * Get the clean all flag.
     *
     * @return bool
     */
    public function getIsCleanAll()
    {
        return $this->isCleanAll;
    }
}
