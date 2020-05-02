<?php

namespace HuangYi\Shadowfax\Listeners\OpenEvent;

use HuangYi\Shadowfax\Events\OpenEvent;
use HuangYi\Shadowfax\Http\Request;
use HuangYi\Shadowfax\Listeners\HasHelpers;
use HuangYi\Shadowfax\WebSocket\Connection;
use HuangYi\Shadowfax\WebSocket\ConnectionCollection;

class DelegateToOpenHandler
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\OpenEvent  $event
     * @return void
     */
    public function handle(OpenEvent $event)
    {
        $this->handleWithoutException(function () use ($event) {
            $request = Request::make($event->request);

            if (! $connection = ConnectionCollection::find($event->request->fd)) {
                $connection = Connection::init($event->server, $request);
            }

            $connection->getHandler()->onOpen($connection, $request->getIlluminateRequest());
        });
    }
}
