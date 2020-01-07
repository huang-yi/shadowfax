<?php

namespace HuangYi\Shadowfax\Events;

class StartEvent extends Event
{
    /**
     * Handle the event.
     *
     * @param  mixed  ...$args
     * @return void
     */
    public function handle(...$args)
    {
        $this->outputProcessInfo($args[0]);
    }

    /**
     * Output the process information.
     *
     * @param  \Swoole\Server  $server
     * @return void
     */
    protected function outputProcessInfo($server)
    {
        $this->output->writeln("<info>[√] master process started. [{$server->master_pid}]</info>");

        shadowfax_set_process_name(sprintf(
            '%s: master process %s:%d',
            $this->getName(),
            $server->host,
            $server->port
        ));
    }
}
