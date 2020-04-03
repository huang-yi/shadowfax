<?php

namespace HuangYi\Shadowfax\Listeners\MessageEvent;

use HuangYi\Shadowfax\Events\MessageEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;
use HuangYi\Shadowfax\WebSocket\ConnectionCollection;
use HuangYi\Shadowfax\WebSocket\RawMessage;

class DelegateToMessageHandler
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        $this->handleWithoutException(function ($app) use ($event) {
            if ($connection = ConnectionCollection::find($event->frame->fd)) {
                list($connection, $handler) = $connection;

                $message = $this->createMessage($event->frame);

                $handler->onMessage($connection, $message);
            }
        });
    }

    /**
     * Create a message instance.
     *
     * @param  \Swoole\WebSocket\Frame  $frame
     * @return \HuangYi\Shadowfax\Contracts\WebSocket\Message
     */
    protected function createMessage($frame)
    {
        $class = $this->config('websocket.message', RawMessage::class);

        return new $class($frame->data, $frame->opcode);
    }
}
