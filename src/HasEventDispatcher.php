<?php

namespace HuangYi\Shadowfax;

use HuangYi\Shadowfax\Contracts\EventDispatcher;

trait HasEventDispatcher
{
    /**
     * The event dispatcher instance.
     *
     * @var \HuangYi\Shadowfax\Contracts\EventDispatcher
     */
    protected $events;

    /**
     * Dispatch a event.
     *
     * @param  string  $event
     * @param  mixed  ...$params
     * @return void
     */
    public function dispatch(string $event, ...$params)
    {
        if ($this->getEvents()) {
            $this->getEvents()->dispatch(new $event(...$params));
        }
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param  \HuangYi\Shadowfax\Contracts\EventDispatcher  $events
     * @return $this
     */
    public function setEvents(EventDispatcher $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \HuangYi\Shadowfax\Contracts\EventDispatcher
     */
    public function getEvents()
    {
        return $this->events;
    }
}
