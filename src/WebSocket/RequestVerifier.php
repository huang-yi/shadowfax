<?php

namespace HuangYi\Shadowfax\WebSocket;

use HuangYi\Shadowfax\Http\Request;

class RequestVerifier
{
    /**
     * Verify the method.
     *
     * @param  \HuangYi\Shadowfax\Http\Request  $request
     * @return bool
     */
    public function verifyMethod(Request $request)
    {
        return $request->getIlluminateRequest()->getMethod() == 'GET';
    }

    public function verifyProtocolVersion(Request $request)
    {
        return $request->getIlluminateRequest()->getProtocolVersion()
    }
}
