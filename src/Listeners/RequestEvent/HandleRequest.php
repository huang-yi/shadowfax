<?php

namespace HuangYi\Shadowfax\Listeners\RequestEvent;

use HuangYi\Shadowfax\Events\RequestEvent;
use HuangYi\Shadowfax\Http\Kernel;
use HuangYi\Shadowfax\Http\Request;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class HandleRequest
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\RequestEvent  $event
     * @return void
     */
    public function handle(RequestEvent $event)
    {
        $this->handleWithoutException(function ($app) use ($event) {
            $response = $app->make(Kernel::class)->handle(
                $request = Request::make($event->request)
            );

            $response->send($event->response);

            $this->outputAccessLog($request, $response);
        });
    }

    /**
     * Output the access log.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @param  \HuangYi\Shadowfax\Http\Response  $response
     * @return void
     */
    protected function outputAccessLog($request, $response)
    {
        if (! $this->config('access_log')) {
            return;
        }

        $this->output(sprintf(
            '[%s] %s [%d]: %s %s%s',
            date('Y-m-d H:i:s'),
            $request->getIlluminateRequest()->ip(),
            $response->getIlluminateResponse()->getStatusCode(),
            $request->getIlluminateRequest()->getMethod(),
            $request->getIlluminateRequest()->fullUrl(),
            $this->calcRequestTime($request)
        ));
    }

    /**
     * Calculate the request time.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @return string
     */
    protected function calcRequestTime($request)
    {
        $start = $request->getIlluminateRequest()->server('REQUEST_TIME_FLOAT');

        if (! $start) {
            return '';
        }

        return ' ['.round((microtime(true) - $start) * 1000, 2).'ms]';
    }
}
