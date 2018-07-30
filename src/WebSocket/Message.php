<?php

namespace HuangYi\Swoole\WebSocket;

use HuangYi\Swoole\Contracts\MessageContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Message implements Arrayable, MessageContract, Jsonable
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
     * @param mixed $data
     * @return static
     */
    public static function make($event, $data = null)
    {
        return new static($event, $data);
    }

    /**
     * Message.
     *
     * @param string $event
     * @param mixed $data
     */
    public function __construct($event, $data = null)
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

    /**
     * Convert message to array.
     *
     * @return array
     */
    public function toArray()
    {
        $message = [
            'event' => $this->event,
        ];

        if (! is_null($this->data)) {
            $message['data'] = $this->data;
        }

        return $message;
    }

    /**
     * Convert message to json.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        $message = $this->toArray();

        return json_encode($message, $options);
    }

    /**
     * Convert message to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
