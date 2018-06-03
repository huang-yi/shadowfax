<?php

namespace HuangYi\Http\Tasks;

class EmitTask extends TaskAbstract
{
    /**
     * EmitTask task handler.
     *
     * @param \Swoole\Server $server
     * @param int $taskId
     * @param int $srcWorkerId
     * @return void
     */
    public function handle($server, $taskId, $srcWorkerId)
    {
        $server->push($this->data['to'], $this->data['message']);
    }
}
