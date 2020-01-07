<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Abstracts that need to be rebound
    |--------------------------------------------------------------------------
    |
    | The abstracts listed here will be rebound after each request.
    |
    */

    'abstracts' => [
        'cookie', 'session', 'session.store', 'redirect', 'auth', 'auth.driver',
        Illuminate\Session\Middleware\StartSession::class,
    ],

];
