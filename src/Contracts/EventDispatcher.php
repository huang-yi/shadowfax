<?php

namespace HuangYi\Shadowfax\Contracts;

use Psr\EventDispatcher\EventDispatcherInterface;

interface EventDispatcher extends EventDispatcherInterface
{
    /**
     * Add an event listener.
     *
     * @param  string|object  $event
     * @param  object  $listener
     * @param  int  $priority
     * @return void
     */
    public function listen($event, object $listener, int $priority = 0);
}
