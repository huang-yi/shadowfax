<?php

namespace HuangYi\Shadowfax\WebSocket;

use Closure;

class VerifiesRequest
{
    /**
     * Verifies the WebSocket request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        (new RequestVerifier($request))->verify();

        return $next($request);
    }
}
