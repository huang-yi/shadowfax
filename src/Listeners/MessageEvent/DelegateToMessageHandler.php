<?php

namespace HuangYi\Shadowfax\Listeners\MessageEvent;

use HuangYi\Shadowfax\Contracts\WebSocket\Handler;
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

                $message = $this->createMessage($event->frame, $handler);

                $handler->onMessage($connection, $message);
            }
        });
    }

    /**
     * Create a message instance.
     *
     * @param  \Swoole\WebSocket\Frame  $frame
     * @param  \HuangYi\Shadowfax\Contracts\WebSocket\Handler  $handler
     * @return \HuangYi\Shadowfax\Contracts\WebSocket\Message
     */
    protected function createMessage($frame, Handler $handler)
    {
        if (method_exists($handler, 'messageClass')) {
            $class = $handler->messageClass();
        } else {
            $class = $this->config('websocket.message', RawMessage::class);
        }

        return new $class($frame->data, $frame->opcode);
    }
}
