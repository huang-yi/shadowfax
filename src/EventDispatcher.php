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
     * The sorted event listener map.
     *
     * @var array
     */
    protected $sorted = [];

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

        unset($this->sorted[$event]);

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
        foreach ($this->getListeners($event) as $listener) {
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

    /**
     * Get event listeners.
     *
     * @param  string|object  $event
     * @return array
     */
    public function getListeners($event)
    {
        if (is_object($event)) {
            $event = get_class($event);
        }

        if (! $this->hasEvent($event)) {
            return [];
        }

        if (isset($this->sorted[$event])) {
            return $this->sorted[$event];
        }

        $priorities = $this->listen[$event];

        krsort($priorities);

        $listeners = [];

        foreach ($priorities as $items) {
            $listeners = array_merge($listeners, $items);
        }

        return $this->sorted[$event] = $listeners;
    }
}
