<?php

namespace HuangYi\Shadowfax\Events;

use Exception;
use HuangYi\Shadowfax\ApplicationFactory;
use HuangYi\Shadowfax\Config;
use HuangYi\Shadowfax\Http\Kernel;
use HuangYi\Shadowfax\Http\Request;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class RequestEvent extends Event
{
    /**
     * Handle the event.
     *
     * @param  mixed  ...$args
     * @return void
     */
    public function handle(...$args)
    {
        $app = $this->appFactory()->make();

        try {
            $response = $app->make(Kernel::class)->handle(
                $request = Request::make($args[0])
            );

            $response->send($args[1]);

            $this->outputRequestInfo($request, $response);
        } catch (Exception $e) {
            $app[ExceptionHandler::class]->report($e);
        } catch (Throwable $e) {
            $app[ExceptionHandler::class]->report(new FatalThrowableError($e));
        }

        $this->appFactory()->recycle($app);
    }

    /**
     * Output the process information.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @param  \HuangYi\Shadowfax\Http\Response  $response
     * @return void
     */
    protected function outputRequestInfo($request, $response)
    {
        if (! $this->getConfig('access_log')) {
            return;
        }

        $this->output->writeln(sprintf(
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

        return ' ['.intval((microtime(true) - $start) * 1000).'ms]';
    }
}
