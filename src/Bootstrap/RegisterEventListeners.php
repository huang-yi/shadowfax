<?php

namespace HuangYi\Shadowfax\Bootstrap;

use HuangYi\Shadowfax\Shadowfax;

class RegisterEventListeners
{
    /**
     * The event listener mappings.
     *
     * @var array
     */
    protected $listen = [
        \HuangYi\Shadowfax\Events\AppPoppedEvent::class => [
            \HuangYi\Shadowfax\Listeners\AppPoppedEvent\RunBeforeCleaners::class,
        ],

        \HuangYi\Shadowfax\Events\AppPushingEvent::class => [
            \HuangYi\Shadowfax\Listeners\AppPushingEvent\CleanControllers::class,
            \HuangYi\Shadowfax\Listeners\AppPushingEvent\RunAfterCleaners::class,
        ],

        \HuangYi\Shadowfax\Events\CloseEvent::class => [
            \HuangYi\Shadowfax\Listeners\CloseEvent\DelegateToCloseHandler::class,
        ],

        \HuangYi\Shadowfax\Events\ControllerRequestEvent::class => [
            \HuangYi\Shadowfax\Listeners\ControllerRequestEvent\HandleInstruction::class,
        ],

        \HuangYi\Shadowfax\Events\FrameworkBootstrappedEvent::class => [
            \HuangYi\Shadowfax\Listeners\FrameworkBootstrappedEvent\OverrideDatabaseManager::class,
            \HuangYi\Shadowfax\Listeners\FrameworkBootstrappedEvent\OverrideRedisManager::class,
        ],

        \HuangYi\Shadowfax\Events\HandshakeEvent::class => [
            \HuangYi\Shadowfax\Listeners\HandshakeEvent\HandleHandshake::class,
        ],

        \HuangYi\Shadowfax\Events\ManagerStartEvent::class => [
            \HuangYi\Shadowfax\Listeners\ManagerStartEvent\SetManagerProcessName::class,
            \HuangYi\Shadowfax\Listeners\ManagerStartEvent\OutputManagerProcessStartedStatus::class,
        ],

        \HuangYi\Shadowfax\Events\ManagerStopEvent::class => [
            \HuangYi\Shadowfax\Listeners\ManagerStopEvent\OutputManagerProcessStoppedStatus::class,
        ],

        \HuangYi\Shadowfax\Events\MessageEvent::class => [
            \HuangYi\Shadowfax\Listeners\MessageEvent\DelegateToMessageHandler::class,
        ],

        \HuangYi\Shadowfax\Events\OpenEvent::class => [
            \HuangYi\Shadowfax\Listeners\OpenEvent\DelegateToOpenHandler::class,
        ],

        \HuangYi\Shadowfax\Events\RequestEvent::class => [
            \HuangYi\Shadowfax\Listeners\RequestEvent\HandleRequest::class,
        ],

        \HuangYi\Shadowfax\Events\ShutdownEvent::class => [
            \HuangYi\Shadowfax\Listeners\ShutdownEvent\OutputShutdownStatus::class,
        ],

        \HuangYi\Shadowfax\Events\StartEvent::class => [
            \HuangYi\Shadowfax\Listeners\StartEvent\SetMasterProcessName::class,
            \HuangYi\Shadowfax\Listeners\StartEvent\OutputMasterProcessStartedStatus::class,
        ],

        \HuangYi\Shadowfax\Events\TaskEvent::class => [
            \HuangYi\Shadowfax\Listeners\TaskEvent\HandleTask::class,
        ],

        \HuangYi\Shadowfax\Events\WorkerStartEvent::class => [
            \HuangYi\Shadowfax\Listeners\WorkerStartEvent\ClearCaches::class,
            \HuangYi\Shadowfax\Listeners\WorkerStartEvent\SetWorkerProcessName::class,
            \HuangYi\Shadowfax\Listeners\WorkerStartEvent\CreateAppPool::class,
            \HuangYi\Shadowfax\Listeners\WorkerStartEvent\SetBufferOutputSizeToResponse::class,
            \HuangYi\Shadowfax\Listeners\WorkerStartEvent\OutputWorkerProcessStartedStatus::class,
        ],

        \HuangYi\Shadowfax\Events\WorkerStopEvent::class => [
            \HuangYi\Shadowfax\Listeners\WorkerStopEvent\OutputWorkerProcessStoppedStatus::class,
        ],
    ];

    /**
     * Register the event listeners.
     *
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @return void
     */
    public function bootstrap(Shadowfax $shadowfax)
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $shadowfax['events']->listen($event, new $listener);
            }
        }
    }
}
