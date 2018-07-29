<?php

namespace HuangYi\Swoole\Tasks;

class BroadcastTask extends TaskAbstract
{
    /**
     * Broadcast task handler.
     *
     * @param \Swoole\Server $server
     * @param int $taskId
     * @param int $srcWorkerId
     * @return void
     */
    public function handle($server, $taskId, $srcWorkerId)
    {
        $clients = array_get($this->data, 'clients', $server->connections);

        $excepts = null;

        if (isset($this->data['excepts'])) {
            $excepts = array_map('intval', (array) $this->data['excepts']);
        }

        foreach ($clients as $socketId) {
            if (! $server->exist($socketId)) {
                continue;
            }

            if ($excepts && in_array($socketId, $excepts, true)) {
                continue;
            }

            $server->push($socketId, $this->data['message']);
        }
    }
}
