# Laravel-Swoole-Http

[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://api.travis-ci.org/huang-yi/laravel-swoole-http.svg)](https://travis-ci.org/huang-yi/laravel-swoole-http)

A high performance HTTP server based on [Swoole](http://www.swoole.com/). And now, it supports [websocket server](#Websocket). 

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

If your Laravel/Lumen version is less than 5.5, you need to register the service provider manually:

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

Otherwise, the package will automatically register the service provider.

3. Configurations

You need to publish the configuration file:

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

The options of the server. Get more information from the [official documents](https://www.swoole.co.uk/docs/modules/swoole-server/configuration).

> Notice: The value of `task_worker_num` must be greater than 0 if you are using websocket server or task process.

### resets

This option allows you to reset some service providers or some instances in IoC container after every request. This option can help developers avoid pollution problems caused by singleton. Such as `auth`.

### message_parser

The websocket message parser. You can replace this option with a custom parser.

> Notice: The custom parser must implement the `HuangYi\Swoole\Contracts\ParserContract`. And the `parse` method must return an object that implements the `HuangYi\Swoole\Contracts\MessageContract`.

### namespace_redis

This option controls the redis connection that store the namespaces of websocket.

Click here for more information about the [namespaces](#namespaces).

### tables

This option defines all the swoole tables. Such as:

```php
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
]
```

- `name`: Defines the table name.
- `size`: Defines the maximum number of rows.
- `columns`: Defines the table's columns. Format: [`column_name`, `column_type`, `column_length`]. The values of `column_type`: `int`, `integer`, `string`, `varchar`, `char`, `float`.

Click here for more information about the [tables](#tables).

### watcher

This option is used for file watcher. You can use this command to enter watch mode.

```sh
php artisan swoole:server watch
```

`directories`：Defines the list of directories being watched;

`excluded_directories`：Defines a list of directories not being watched;

`suffixes`：Defines the list of file suffixes being watched.

## Commands

The package provides an artisan command to manage the swoole server.

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

## Websocket

To create a websocket server, you only need to change the `driver` option to "websocket". And the websocket server can also act as a HTTP server.

> Notice: The websocket can only run in Laravel framework. And you must ensure the value of the `task_worker_num` is greater than 0.

### Namespaces

It's like the [Socket.IO](https://socket.io/docs/rooms-and-namespaces/#namespaces). This package allows you to "namespace" your sockets.

This is a useful feature to minimize the number of resources (TCP connections) and at the same time separate concerns within your application by introducing separation between communication channels.

You can use the `HuangYi\Swoole\Facades\WebsocketNamespace` to control the namespaces:

- `void WebsocketNamespace::getPath(int $socketId)`: Get the path of namespace by socketId.
- `array WebsocketNamespace::getClients(string $path)`: Get all clients in the namespace by path.
- `void WebsocketNamespace::broadcast(string $path, \HuangYi\Swoole\Contracts\MessageContract $message, array|int $excepts = null)`: Broadcast a message by path.
- `void WebsocketNamespace::emit(int $socketId, \HuangYi\Swoole\Contracts\MessageContract $message)`: Emit a message to client.
- `void WebsocketNamespace::flush(string $path)`: Flush namespace by path.
- `void WebsocketNamespace::flushAll()`: Flush all namespaces.

This is an example of namespace:

```php
<?php

use HuangYi\Swoole\Facades\WebsocketNamespace;
use HuangYi\Swoole\Websocket\Message\Message;

class ChattingRoom
{
    /**
     * Send a group message.
     * 
     * @param \HuangYi\Swoole\Websocket\Message\Message $message
     * @return void
     */
    public function sendGroupMessage(Message $message)
    {
        $socketId = $message->getSocketId();

        // You can get the path of namespace by socketId.
        $path = WebsocketNamespace::getPath($socketId);

        $broadcastMessage = Message::make('send group message', $message->getData('content'));

        // You can broadcast a message to all clients in the namespace by path.
        WebsocketNamespace::broadcast($path, $broadcastMessage, $socketId);
    }

    /**
     * Send a private message.
     * 
     * @param \HuangYi\Swoole\Websocket\Message\Message $message
     * @return void
     */
    public function sendPrivateMessage(Message $message)
    {
        $to = $message->getData('to');

        $privateMessage = Message::make('send private message', [
            'from' => $message->getSocketId(),
            'content' => $message->getData('content'),
        ]);

        // You can emit a message by socketId.
        WebsocketNamespace::emit($to, $privateMessage);
    }
}

```

### Websocket Route

You can define namespace using websocket route facade. The websocket router is inherited from the `Illuminate\Routing\Router`.

```php
<?php

use HuangYi\Swoole\Facades\WebsocketNamespace;
use HuangYi\Swoole\Facades\WebsocketRoute;
use HuangYi\Swoole\Websocket\Message\Message;
use Illuminate\Http\Request;

WebsocketRoute::path('/chatting_room', function (Request $request) {
    $socketId = app('swoole.http.request')->fd;
    $path = $request->path();
    $message = Message::make('user join', "User [{$socketId}] joined.");

    WebsocketNamespace::broadcast($path, $message, $socketId);
});

```

### Message Route

The message route is used to specify a handler for a websocket event.

```php
<?php
use HuangYi\Swoole\Facades\MessageRoute;
use HuangYi\Swoole\Websocket\Message\Message;

// Using closure.
MessageRoute::on('send private message', function (Message $message) {
    // Do something.
});

// Using controller.
MessageRoute::on('send group message', 'ChattingRoom@sendGroupMessage');

```

The handler method will be injected a message entity: `HuangYi\Swoole\Websocket\Message\Message`.

## Tables

The Swoole Table can help developers to share data between worker processes. You can define the structure of a Swoole Table in the configuration file.

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

Click here for more information about the [Swoole Tables](#https://www.swoole.co.uk/docs/modules/swoole-table).

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

Http configurations:

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
    
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        # IF https
        # proxy_set_header X-Forwarded-Proto https;

        proxy_pass http://127.0.0.1:1215$suffix;
    }
}
```

Websocket configurations:

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    '' close;
}

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
    
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;

        # IF https
        # proxy_set_header X-Forwarded-Proto https;

        proxy_pass http://127.0.0.1:1215$suffix;
    }
}
```

## Tips

- Never use these functions: `sleep()`、`exit()`、`die()`.
- Be careful of the singletons.
