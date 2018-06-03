<?php

namespace HuangYi\Http\Websocket\Message;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Message extends MessageAbstract implements Arrayable, Jsonable
{
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
