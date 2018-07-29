<?php

namespace HuangYi\Swoole\WebSocket;

use HuangYi\Swoole\Contracts\MessageContract;

class Event
{
    /**
     * Name.
     *
     * @var string
     */
    protected $name;

    /**
     * Callback.
     *
     * @var array|\Closure
     */
    protected $callback;

    /**
     * Event constructor.
     * @param string $name
     * @param array|\Closure $callback
     * @return void
     */
    public function __construct($name, $callback)
    {
        $this->name = $name;
        $this->callback = $callback;
    }

    /**
     * Fire event.
     *
     * @param \HuangYi\Swoole\Contracts\MessageContract $message
     * @return void
     */
    public function fire(MessageContract $message)
    {
        call_user_func($this->callback, $message);
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get callback.
     *
     * @return array|\Closure
     */
    public function getCallback()
    {
        return $this->callback;
    }
}
