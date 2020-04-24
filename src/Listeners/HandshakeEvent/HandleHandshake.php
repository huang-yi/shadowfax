<?php

namespace HuangYi\Shadowfax\Listeners\HandshakeEvent;

use HuangYi\Shadowfax\Events\HandshakeEvent;
use HuangYi\Shadowfax\Http\Kernel;
use HuangYi\Shadowfax\Http\Request;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class HandleHandshake
{
    use HasHelpers;

    public function handle(HandshakeEvent $event)
    {
        $this->handleWithoutException(function ($app) use ($event) {
            $request = Request::make($event->request);

            // TODO: verify request and handshake

            $response = $app->make(Kernel::class)->handle($request);

            $response->send($event->response);
        });
    }
}
