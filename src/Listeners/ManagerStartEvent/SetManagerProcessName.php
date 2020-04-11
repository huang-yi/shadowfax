<?php

namespace HuangYi\Shadowfax\Listeners\ManagerStartEvent;

use HuangYi\Shadowfax\Events\ManagerStartEvent;
use HuangYi\Shadowfax\Listeners\HasHelpers;

class SetManagerProcessName
{
    use HasHelpers;

    /**
     * Handle the event.
     *
     * @param  \HuangYi\Shadowfax\Events\ManagerStartEvent  $event
     * @return void
     */
    public function handle(ManagerStartEvent $event)
    {
        @swoole_set_process_name(sprintf(
            '%s: manager process%s',
            $this->getName(),
            $this->getHostAndPortString($event->server)
        ));
    }

    /**
     * Get the host and port string.
     *
     * @param  \Swoole\Server  $server
     * @return string
     */
    protected function getHostAndPortString($server)
    {
        if ($server->mode == SWOOLE_BASE) {
            return " {$server->host}:{$server->port}";
        }

        return '';
    }
}
