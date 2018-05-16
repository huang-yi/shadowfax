<?php

namespace HuangYi\Swoole\Http\Console;

use HuangYi\Swoole\Foundation\ServerCommand;

class HttpServerCommand extends ServerCommand
{
    /**
     * Server name.
     *
     * @return string
     */
    public function server() : string
    {
        return 'http';
    }

    /**
     * Get Pid file path.
     *
     * @return string
     */
    protected function getPidPath() : string
    {
        return $this->laravel['config']['http.options.pid_file'];
    }
}
