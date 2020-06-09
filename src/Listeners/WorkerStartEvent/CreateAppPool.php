<?php

namespace HuangYi\Shadowfax\Listeners\WorkerStartEvent;

use HuangYi\Shadowfax\Events\WorkerStartEvent;
use HuangYi\Shadowfax\Laravel\AppPool;
use HuangYi\Shadowfax\Laravel\CoroutineAppPool;
use HuangYi\Shadowfax\Laravel\FrameworkBootstrapper;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class CreateAppPool
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\WorkerStartEvent  $event
     * @return void
     * @throws \HuangYi\Shadowfax\Exceptions\InvalidFrameworkBootstrapperException
     */
    public function handle(WorkerStartEvent $event)
    {
        $bootstrapper = $this->createBootstrapper($event->server, $event->workerId);

        if ($this->inCoroutine()) {
            $pool = new CoroutineAppPool(
                $bootstrapper,
                $this->config('abstracts', []),
                $this->config('app_pool_capacity', 10)
            );
        } else {
            $pool = new AppPool($bootstrapper, $this->config('abstracts', []));
        }

        $pool->setEvents(shadowfax('events'));

        shadowfax()->instance('app_pool', $pool);
    }

    /**
     * Create the framework bootstrapper.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return \HuangYi\Shadowfax\Laravel\FrameworkBootstrapper
     */
    protected function createBootstrapper($server, $workerId)
    {
        return new FrameworkBootstrapper(
            $this->getBootstrapFile(),
            $this->isTaskWorker($server, $workerId),
            shadowfax('events')
        );
    }

    /**
     * Get the framework bootstrap path.
     *
     * @return string
     */
    protected function getBootstrapFile()
    {
        return $this->config('framework_bootstrapper') ?: shadowfax()->basePath('bootstrap/app.php');
    }
}
