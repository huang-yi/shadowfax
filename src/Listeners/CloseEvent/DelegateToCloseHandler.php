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
        $this->handleWithoutException(function ($app) use ($event) {
            if ($connection = ConnectionCollection::find($event->fd)) {
                list($connection, $handler) = $connection;

                $handler->onClose($connection);
            }
        });

        ConnectionCollection::forget($event->fd);
    }
}
