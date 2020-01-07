<?php

namespace HuangYi\Shadowfax\Server;

use Swoole\Coroutine;

class Stopper extends Action
{
    /**
     * Stop the Shadowfax server.
     *
     * @return void
     */
    public function stop()
    {
        Coroutine::create(function () {
            $client = $this->createControllerClient();

            $client->get('/stop');

            if ($client->errCode !== 0) {
                $this->output->writeln("<error>Cannot connect the Shadowfax controller server. [{$client->errCode}]</error>");

                return;
            }

            if ($client->statusCode !== 200) {
                $this->output->writeln("<error>Failed to stop the Shadowfax server: {$client->body}</error>");

                return;
            }

            $this->output->writeln("<info>The Shadowfax server stopped.</info>");
        });
    }
}
