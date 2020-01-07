<?php

namespace HuangYi\Shadowfax\Server\Events;

class ShutdownEvent extends Event
{
    /**
     * Handle the event.
     *
     * @param  mixed  ...$args
     * @return void
     */
    public function handle(...$args)
    {
        $this->output->writeln("<info>[×] The Shadowfax server stopped.</info>");
    }
}
