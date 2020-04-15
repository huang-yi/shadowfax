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
    protected $listen = [];

    /**
     * Add an event listener.
     *
     * @param  string|object  $event
     * @param  object  $listener
     * @param  int  $priority
     * @return $this
     * @throws \HuangYi\Shadowfax\Exceptions\InvalidListenerException
     */
    public function listen($event, object $listener, int $priority = 0)
    {
        if (is_object($event)) {
            $event = get_class($event);
        }

        if (! method_exists($listener, 'handle')) {
            throw new InvalidListenerException(
                'The listener ['.get_class($listener).'] must have a "handler" method.'
            );
        }

        $this->listen[$event][$priority][] = $listener;

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
        $listeners = $this->listen[get_class($event)] ?? [];

        krsort($listeners);

        foreach ($listeners as $listener) {
            $listener->handle($event);
        }

        return $event;
    }

    /**
     * @param  string|object  $event
     * @return bool
     */
    public function hasEvent($event)
    {
        if (is_object($event)) {
            $event = get_class($event);
        }

        return isset($this->listen[$event]);
    }

    /**
     * Get the event listener map.
     *
     * @return array
     */
    public function getListen()
    {
        return $this->listen;
    }
}
