<?php

namespace HuangYi\Shadowfax\Listeners\HandshakeEvent;

use HuangYi\Shadowfax\Events\HandshakeEvent;
use HuangYi\Shadowfax\Http\Kernel;
use HuangYi\Shadowfax\Http\Request;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class HandleHandshake
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\HandshakeEvent  $event
     * @return void
     */
    public function handle(HandshakeEvent $event)
    {
        $this->handleWithoutException(function ($app) use ($event) {
            $response = $app->make(Kernel::class)->handle(
                $request = Request::make($event->request), true
            );

            $this->formatResponse($response);

            $response->send($event->response);
        });
    }

    /**
     * Format the response.
     *
     * @param  \HuangYi\Shadowfax\Http\Response  $response
     * @return void
     */
    protected function formatResponse($response)
    {
        $status = $response->getIlluminateResponse()->getStatusCode();

        if ($status >= 200 && $status < 300) {
            $response->getIlluminateResponse()->setStatusCode(101);
        }

        $response->getIlluminateResponse()->setContent('');
    }
}
