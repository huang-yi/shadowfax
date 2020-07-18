<?php

namespace HuangYi\Shadowfax\Events;

class AppPoppedEvent
{
    /**
     * The Laravel application instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    public $app;

    /**
     * Create a new AfterReloadEvent instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
}
