# Laravel-Swoole-Http

[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://api.travis-ci.org/huang-yi/laravel-swoole-http.svg)](https://travis-ci.org/huang-yi/laravel-swoole-http)

A high performance HTTP server based on [Swoole](http://www.swoole.com/). And now, it also supports [WebSocket server](websocket.md). 

## Translations

- [中文文档](README-cn.md)

## Requirements

- Laravel/Lumen 5.2+
- ext-swoole 1.9.5+
- ext-inotify (Optional)

## Installation & Setup

1. This package can be installed via composer:

```
$ composer require huang-yi/laravel-swoole-http
```

2. Register service provider(Optional)

You need to register the service provider manually, if your Laravel version is less than 5.5 or you are using Lumen:

```php
<?php
// Framework: Laravel
// File: config/app.php

[
    'providers' => [
        HuangYi\Swoole\SwooleServiceProvider::class,
    ],
];
```

```php
<?php
// Framework: Lumen
// File: bootstrap/app.php

$app->register(HuangYi\Swoole\SwooleServiceProvider::class);
```

3. Configurations

Run this command to publish the configuration file:

```
$ php artisan vendor:publish --provider="HuangYi\Swoole\SwooleServiceProvider"
```

Click here for more information about the [configurations](#configurations).

4. Start server

Run this command to start the server:

```sh
php artisan swoole:server
```

And then, you can visit your website via http://127.0.0.1:1215.

Click here for more information about the [commands](#commands).

## Configurations

### driver

This option controls the default "driver" that will be used to create the server.

Supported: "http", "websocket"

### host

The ip address of the server.

### port

The port of the server.

### options

The swoole server configurations. Get more information from the [official documents](https://www.swoole.co.uk/docs/modules/swoole-server/configuration).

> Notice: The value of `task_worker_num` must be greater than 0 if you use [task](#task).

### resets

This option controls the instances that need to be reset after each request. It helps developers avoid problems caused by singleton, such as `auth`.

### message_parser

The WebSocket message parser. You can replace this option with a custom parser.

> Notice: The custom parser must implement the `HuangYi\Swoole\Contracts\ParserContract`.

### redis

- `connection`: Specify a redis connection to store websocket rooms and clients.
- `prefix`: Set the prefix of redis key.

### tables

Define swoole table structures:

```php
<?php

[
    'tables' => [
        [
            'name' => 'users',
            'size' => 1024,
            'columns' => [
                ['id', 'int', 8],
                ['nickname', 'string', 255],
                ['score', 'float']
            ],
        ],
    ],
];

```

- `name`: Specifies a table name.
- `size`: Defines the maximum number of rows.
- `columns`: Defines the table's columns. Format: [`column_name`, `column_type`, `column_length`]. Column types: `int`, `integer`, `string`, `varchar`, `char`, `float`.

Click here for more information about the [tables](#tables).

### watcher

The file watcher configurations.

`directories`：Defines the list of directories being watched;

`excluded_directories`：Defines a list of directories not being watched;

`suffixes`：Defines the list of file suffixes being watched.

## Commands

This package provides an artisan command to manage the swoole server.

```sh
php artisan swoole:server
```

This command has an "action" argument and the default value is "start".

| Actions | Description |
|:------:|:---:|
| start | Start the swoole server. |
| stop | Stop the swoole server. |
| reload | Reload the swoole server. |
| restart | Restart the swoole server. |
| watch | Enter the watch mode. The swoole server will reload automatically when the watched files changed. |

> Notice: The swoole server can only run in cli environment.

## Tables

The Swoole Table can help developers to share data across worker processes. You can define the table structures in the configuration file.

```php
<?php
use HuangYi\Swoole\Facades\Table;

// Insert a record.
Table::use('users')->set(1, ['id' => 1, 'nickname' => 'Bob', 'score' => 9.5]);

// Query a record.
$bob = Table::use('users')->get(1);
$nickname = Table::use('users')->get(1, 'nickname');

// Truncate a table.
Table::truncate('users');

```

Click here for more information about the [Swoole Tables](https://www.swoole.co.uk/docs/modules/swoole-table).

## Task

Define a task:

```php
<?php

use HuangYi\Swoole\Contracts\TaskContract;
use Illuminate\Support\Facades\Mail;

class SendMailTask implements TaskContract
{
    /**
     * @var array $mail
     */
    protected $mail;

    /**
     * Mail task
     * 
     * @var array $mail
     * @return void
     */
    public function __construct(array $mail)
    {
        $this->mail = $mail;
    }

    /**
     * Task handler.
     *
     * @param \Swoole\Server $server
     * @param int $taskId
     * @param int $srcWorkerId
     * @return void
     */
    public function handle($server, $taskId, $srcWorkerId)
    {
        Mail::to($this->mail['to'])->send($this->mail['view'], $this->mail['data']);
    }
}

```

Send the task to the task worker processes:

```php
<?php

$task = new SendMailTask([
    'to' => 'bob@mail.com',
    'view' => 'mail',
    'data' => [],
]);

app('swoole.server')->task($task);

```

> Notice: To start the task worker processes, the value of `task_worker_num` must be set to greater than 0.

## Nginx

```nginx
server {
    listen 80;
    server_name your.domain;
    root /path/to/laravel/public;
    index index.php;

    location = /index.php {
        # Ensure that there is no such file named "not_exists" in your "public" directory.
        try_files /not_exists @swoole;
    }

    location / {
        try_files $uri $uri/ @swoole;
    }

    location @swoole {
        set $suffix "";
        
        if ($uri = /index.php) {
            set $suffix "/";
        }

        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        proxy_pass http://127.0.0.1:1215$suffix;
    }
}
```

> Notice: Add the ip of swoole server (the default is 127.0.0.1) to the middleware `App\Http\Middleware\TrustProxies`.

## Tips

- Never use these functions: `sleep()`、`exit()`、`die()`.
- Be careful of the singletons.
