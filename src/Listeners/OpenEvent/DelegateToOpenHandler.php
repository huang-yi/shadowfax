<?php

namespace HuangYi\Shadowfax\Listeners\OpenEvent;

use HuangYi\Shadowfax\Events\OpenEvent;
use HuangYi\Shadowfax\Http\Request;
use HuangYi\Shadowfax\Listeners\HasHelpers;
use HuangYi\Shadowfax\WebSocket\Connection;
use HuangYi\Shadowfax\WebSocket\ConnectionCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $this->handleWithoutException(function ($app) use ($event) {
            $request = Request::make($event->request);

            if (! $connection = ConnectionCollection::find($event->request->fd)) {
                ConnectionCollection::add(
                    $connection = Connection::init($event->server, $request)
                );
            }

            $connection->getHandler()->onOpen($connection, $request->getIlluminateRequest());
        });
    }
}
