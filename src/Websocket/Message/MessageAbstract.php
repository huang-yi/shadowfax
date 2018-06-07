<?php

namespace HuangYi\Swoole\Websocket\Message;

use HuangYi\Swoole\Contracts\MessageContract;

abstract class MessageAbstract implements MessageContract
{
    /**
     * Event name.
     *
     * @var string
     */
    protected $event;

    /**
     * Event data.
     *
     * @var array|null
     */
    protected $data;

    /**
     * Socket id.
     *
     * @var int
     */
    protected $socketId;

    /**
     * Make a new message.
     *
     * @param string $event
     * @param array|null $data
     * @return static
     */
    public static function make($event, array $data = null)
    {
        return new static($event, $data);
    }

    /**
     * Message.
     *
     * @param string $event
     * @param array|null $data
     */
    public function __construct($event, array $data = null)
    {
        $this->event = $event;
        $this->data = $data;
    }

    /**
     * Get event.
     *
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get data.
     *
     * @param string $key
     * @param mixed $default
     * @return array|mixed|null
     */
    public function getData($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        return array_get($this->data, $key, $default);
    }

    /**
     * @return int
     */
    public function getSocketId()
    {
        return $this->socketId;
    }

    /**
     * @param int $socketId
     * @return $this
     */
    public function setSocketId($socketId)
    {
        $this->socketId = $socketId;

        return $this;
    }
}
