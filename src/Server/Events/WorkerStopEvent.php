<?php

namespace HuangYi\Shadowfax\Server\Events;

class WorkerStopEvent extends Event
{
    /**
     * Handle the event.
     *
     * @param  mixed  ...$args
     * @return void
     */
    public function handle(...$args)
    {
        $type = $this->isTaskProcess($args[0], $args[1]) ? 'task worker' : 'worker';

        $this->output->writeln("<info>[×] {$type} process stopped. [{$args[0]->worker_pid}]</info>");
    }

    /**
     * Determine if the process is a task process.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return bool
     */
    public function isTaskProcess($server, $workerId)
    {
        return $server->setting['worker_num'] <= $workerId;
    }
}
