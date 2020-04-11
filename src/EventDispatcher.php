<?php

namespace HuangYi\Shadowfax;

use HuangYi\Shadowfax\Contracts\EventDispatcher as EventDispatcherContract;
use HuangYi\Shadowfax\Exceptions\InvalidListenerException;

class EventDispatcher implements EventDispatcherContract
{
    /**
     * The event listener map.
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Add an event listener.
     *
     * @param  string  $event
     * @param  object  $listener
     * @return $this
     * @throws \HuangYi\Shadowfax\Exceptions\InvalidListenerException
     */
    public function listen(string $event, object $listener)
    {
        if (! method_exists($listener, 'handle')) {
            throw new InvalidListenerException(
                'The listener ['.get_class($listener).'] must have a "handler" method.'
            );
        }

        $this->listeners[$event][get_class($listener)] = $listener;

        return $this;
    }

    /**
     * Dispatch a event.
     *
     * @param object $event
     * @return object
     */
    public function dispatch(object $event)
    {
        $listeners = $this->listeners[get_class($event)] ?? [];

        foreach ($listeners as $listener) {
            $listener->handle($event);
        }

        return $event;
    }
}
