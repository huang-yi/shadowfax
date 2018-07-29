<?php

namespace HuangYi\Swoole;

use Illuminate\Support\Manager;

class ServerManager extends Manager
{
    /**
     * Create http driver.
     *
     * @return \HuangYi\Swoole\HttpServer
     */
    protected function createHttpDriver()
    {
        list($host, $port, $options) = $this->getServerConfig();

        return (new HttpServer($host, $port, $options))
            ->setContainer($this->app);
    }

    /**
     * Create websocket driver.
     *
     * @return \HuangYi\Swoole\WebSocketServer
     * @throws \HuangYi\Swoole\Exceptions\FrameworkUnsupportedException
     */
    protected function createWebsocketDriver()
    {
        list($host, $port, $options) = $this->getServerConfig();

        return (new WebSocketServer($host, $port, $options))
            ->setContainer($this->app);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['swoole.driver'];
    }

    /**
     * Get server config.
     *
     * @return array
     */
    protected function getServerConfig()
    {
        return array_values($this->app['config']->get(
            ['swoole.host', 'swoole.port', 'swoole.options']
        ));
    }
}
