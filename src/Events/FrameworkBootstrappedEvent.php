<?php

namespace HuangYi\Shadowfax\Events;

use Illuminate\Contracts\Container\Container;

class FrameworkBootstrappedEvent
{
    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    public $app;

    /**
     * Create a new FrameworkBootstrappedEvent instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }
}
