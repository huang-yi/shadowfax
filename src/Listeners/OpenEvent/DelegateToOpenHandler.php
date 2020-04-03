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
            $connection = new Connection($event->request->fd, $event->server);

            try {
                $handler = $app['shadowfax.websocket']->findHandler(
                    $request = Request::make($event->request)
                );

                ConnectionCollection::add($connection, $handler);

                $handler->onOpen($connection, $request->getIlluminateRequest());
            } catch (NotFoundHttpException $e) {
                $connection->close();
            }
        });
    }
}
