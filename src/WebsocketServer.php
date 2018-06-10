<?php

namespace HuangYi\Swoole;

use HuangYi\Swoole\Exceptions\FrameworkUnsupportedException;
use HuangYi\Swoole\Transformers\RequestTransformer;
use HuangYi\Swoole\Websocket\Message\Kernel as MessageKernel;
use Swoole\Websocket\Server as SwooleWebsocketServer;

class WebsocketServer extends HttpServer
{
    /**
     * The websocket kernel.
     *
     * @var \Illuminate\Contracts\Http\Kernel
     */
    protected $websocketKernel;

    /**
     * Server events.
     *
     * @var array
     */
    protected $events = ['request', 'open', 'message'];

    /**
     * Http Server.
     *
     * @param string $host
     * @param int $port
     * @param array $options
     * @throws \HuangYi\Swoole\Exceptions\FrameworkUnsupportedException
     */
    public function __construct($host, $port, array $options)
    {
        if ($this->isLumen()) {
            throw new FrameworkUnsupportedException(
                "Websocket server doesn't support Laravel."
            );
        }

        $options = $this->initTaskWorkerNum($options);

        parent::__construct($host, $port, $options);
    }

    /**
     * Init 'task_worker_num' option.
     *
     * @param array $options
     * @return array
     */
    protected function initTaskWorkerNum(array $options)
    {
        if (! isset($options['task_worker_num']) || $options['task_worker_num'] <= 0) {
            $options['task_worker_num'] = 1;
        }

        return $options;
    }

    /**
     * Define swoole http server class.
     *
     * @return string
     */
    public function swooleServer()
    {
        return SwooleWebsocketServer::class;
    }

    /**
     * The listener of "workerStart" event.
     *
     * @return void
     */
    public function onWorkerStart()
    {
        parent::onWorkerStart();

        $this->websocketKernel = $this->container['swoole.websocket.kernel'];
        $this->websocketKernel->bootstrap();
    }

    /**
     * The listener of "open" event.
     *
     * @param \Swoole\Websocket\Server $server
     * @param \Swoole\Http\Request $request
     * @return void
     */
    public function onOpen($server, $request)
    {
        $this->container['events']->fire('swoole.opening', func_get_args());

        $this->container->instance('swoole.http.request', $request);

        $illuminateRequest = RequestTransformer::make($request)->toIlluminateRequest();

        $illuminateResponse = $this->websocketKernel->handle($illuminateRequest);

        $this->websocketKernel->terminate($illuminateRequest, $illuminateResponse);

        $this->container['events']->fire('swoole.opened', func_get_args());
    }

    /**
     * The listener of "message" event.
     *
     * @param \Swoole\Websocket\Server $server
     * @param \Swoole\Websocket\Frame $frame
     * @return void
     */
    public function onMessage($server, $frame)
    {
        $this->container['events']->fire('swoole.messaging', func_get_args());

        (new MessageKernel($this->container))->handle($frame);

        $this->container['events']->fire('swoole.messaged', func_get_args());
    }

    /**
     * The listener of "close" event.
     *
     * @param \Swoole\Server $server
     * @param int $socketId
     * @param int $reactorId
     * @return void
     */
    public function onClose($server, $socketId, $reactorId)
    {
        $this->container['swoole.websocket.namespace']->leave($socketId);

        $this->container['events']->fire('swoole.close', func_get_args());
    }

    /**
     * The listener of "shutdown" event.
     *
     * @return void
     */
    public function onShutdown()
    {
        $this->container['swoole.websocket.namespace']->flushAll();

        $this->container['events']->fire('swoole.shutdown', func_get_args());
    }

    /**
     * Get server name
     *
     * @return string
     */
    protected function getServerName()
    {
        return 'swoole-websocket-server';
    }
}
