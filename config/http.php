<?php

return [

    'host' => env('HTTP_HOST', '127.0.0.1'),

    'port' => env('HTTP_PORT', '1215'),

    /*
    |--------------------------------------------------------------------------
    | Swoole http server configurations.
    |--------------------------------------------------------------------------
    |
    | @see https://wiki.swoole.com/wiki/page/274.html
    |
    */

    'options' => [

        'pid_file' => env('HTTP_OPTIONS_PID_FILE', base_path('storage/logs/http.pid')),

        'log_file' => env('HTTP_OPTIONS_LOG_FILE', base_path('storage/logs/http.log')),

        'daemonize' => env('HTTP_OPTIONS_DAEMONIZE', 1),

    ],

];
