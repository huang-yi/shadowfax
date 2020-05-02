<?php

namespace HuangYi\Shadowfax\Listeners\CloseEvent;

use HuangYi\Shadowfax\Events\CloseEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;
use HuangYi\Shadowfax\WebSocket\ConnectionCollection;
use Swoole\WebSocket\Server;

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
        if (! $event->server instanceof Server) {
            return;
        }

        $this->handleWithoutException(function () use ($event) {
            if ($connection = ConnectionCollection::find($event->fd)) {
                $connection->getHandler()->onClose($connection);
            }
        });

        ConnectionCollection::forget($event->fd);
    }
}
