<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server driver
    |--------------------------------------------------------------------------
    |
    | Supported: "http", "websocket"
    |
    */

    'driver' => 'http',

    /*
    |--------------------------------------------------------------------------
    | Server host
    |--------------------------------------------------------------------------
    |
    | The ip address of the server.
    |
    */

    'host' => env('SWOOLE_HOST', '127.0.0.1'),

    /*
    |--------------------------------------------------------------------------
    | Server host
    |--------------------------------------------------------------------------
    |
    | The port of the server.
    |
    */

    'port' => env('SWOOLE_PORT', '1215'),

    /*
    |--------------------------------------------------------------------------
    | Server configurations
    |--------------------------------------------------------------------------
    |
    | @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration
    |
    */

    'options' => [
        'pid_file' => env('SWOOLE_OPTIONS_PID_FILE', base_path('storage/logs/swoole.pid')),

        'log_file' => env('SWOOLE_OPTIONS_LOG_FILE', base_path('storage/logs/swoole.log')),

        'daemonize' => env('SWOOLE_OPTIONS_DAEMONIZE', 1),

        'worker_num' => env('SWOOLE_OPTIONS_WORKER_NUM', swoole_cpu_num()),

        // This value must be greater than 0 if use websocket or task.
        'task_worker_num' => env('SWOOLE_OPTIONS_TASK_WORKER_NUM', 0),
    ],

    /*
    |----------------------------------------------------------------------
    | Resets
    |----------------------------------------------------------------------
    |
    | This option allows you to reset some service providers or some
    | instances in IoC container.
    |
    */

    'resets' => [
        'auth',
        'auth.driver',
    ],

    /*
    |--------------------------------------------------------------------------
    | Websocket message parser class
    |--------------------------------------------------------------------------
    |
    | This class allows you to customize the message format of websocket.
    | And it must implement "HuangYi\Swoole\Contracts\ParserContract".
    |
    */

    'message_parser' => HuangYi\Swoole\Websocket\Message\JsonParser::class,

    /*
    |--------------------------------------------------------------------------
    | Websocket namespace redis connection
    |--------------------------------------------------------------------------
    |
    | You may specify a redis connection that should be used to manage the
    | websocket namespaces.
    |
    */

    'namespace_redis' => env('SWOOLE_NAMESPACE_REDIS', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Swoole tables
    |--------------------------------------------------------------------------
    |
    | "name": Table name.
    | "columns": Table columns.
    |
    | Define a column:
    |     [column_name, column_type, column_length]
    | Supported column types:
    |     "int", "integer", "string", "varchar", "char", "float"
    |
    */

    'tables' => [
        // [
        //     'name' => 'users',
        //     'columns' => [
        //         ['id', 'int', 8],
        //         ['nickname', 'string', 255],
        //         ['score', 'float'],
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File watcher configurations
    |--------------------------------------------------------------------------
    |
    | "directories": The directories should be watched.
    | "excluded_directories": The directories should not be watched.
    | "suffixes": The file suffix to be watched should be in this array.
    |
    */

    'watcher' => [
        'directories' => [
            base_path(),
        ],

        'excluded_directories' => [
            base_path('storage/'),
        ],

        'suffixes' => [
            '.php', '.env',
        ],
    ],
];
