<?php

namespace HuangYi\Shadowfax\Server;

use Swoole\Coroutine;

class Reloader extends Action
{
    /**
     * Stop the Shadowfax server.
     *
     * @return void
     */
    public function reload()
    {
        Coroutine::create(function () {
            $client = $this->createControllerClient();

            if ($this->input->getOption('task') === false) {
                $this->output->writeln("<info>Reload all the Shadowfax worker processes.</info>");

                $client->get('/reload');
            } else {
                $this->output->writeln("<info>Reload all the Shadowfax task worker processes.</info>");

                $client->get('/reload-task');
            }

            if ($client->errCode !== 0) {
                $this->output->writeln("<error>Cannot connect the Shadowfax controller server. [{$client->errCode}]</error>");

                return;
            }

            if ($client->statusCode !== 200) {
                $this->output->writeln("<error>Failed to reload the Shadowfax server: {$client->body}</error>");

                return;
            }

            $this->output->writeln("<info>The Shadowfax server reloaded.</info>");
        });
    }
}
