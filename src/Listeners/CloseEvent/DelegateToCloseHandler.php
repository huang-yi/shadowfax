<?php

namespace HuangYi\Shadowfax\Listeners\CloseEvent;

use HuangYi\Shadowfax\Events\CloseEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;
use HuangYi\Shadowfax\WebSocket\ConnectionCollection;

class DelegateToCloseHandler
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\CloseEvent  $event
     * @return void
     */
    public function handle(CloseEvent $event)
    {
        $this->closeWebSocketConnection($event);
    }

    /**
     * Close the WebSocket connection.
     *
     * @param  \HuangYi\Shadowfax\Events\CloseEvent  $event
     * @return void
     */
    protected function closeWebSocketConnection(CloseEvent $event)
    {
        if (! $connection = ConnectionCollection::find($event->fd)) {
            return;
        }

        $this->handleWithoutException(function () use ($connection) {
            $connection->getHandler()->onClose($connection);
        });

        ConnectionCollection::forget($event->fd);
    }
}
