<?php

namespace HuangYi\Shadowfax\Events;

class ManagerStopEvent extends Event
{
    /**
     * Handle the event.
     *
     * @param  mixed  ...$args
     * @return void
     */
    public function handle(...$args)
    {
        $this->output->writeln("<info>[×] manager process stopped. [{$args[0]->manager_pid}]</info>");
    }
}
