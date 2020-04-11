<?php

namespace HuangYi\Shadowfax\Contracts;

use Psr\EventDispatcher\EventDispatcherInterface;

interface EventDispatcher extends EventDispatcherInterface
{
    /**
     * Add an event listener.
     *
     * @param  string  $event
     * @param  object  $listener
     * @return void
     */
    public function listen(string $event, object $listener);
}
