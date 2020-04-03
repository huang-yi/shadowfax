<?php

namespace HuangYi\Shadowfax\Events;

class ControllerRequestEvent
{
    /**
     * The Swoole http request.
     *
     * @var \Swoole\Http\Request
     */
    public $request;

    /**
     * The Swoole http response.
     *
     * @var \Swoole\Http\Response
     */
    public $response;

    /**
     * Create a new ControllerRequestEvent instance.
     *
     * @param  \Swoole\Http\Request  $request
     * @param  \Swoole\Http\Response  $response
     * @return void
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
