# Laravel-Swoole-Http

A high performance HTTP server based on [Swoole](http://www.swoole.com/). And now, it also supports [websocket server](#Websocket). 

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

Click here for more information about the [configurations](#Configurations).

4. Start server

Run this command to start the server:

```sh
php artisan swoole:server
```

And then, you can visit your website via http://127.0.0.1:1215.

Click here for more information about the [commands](#Commands).

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

### reset_providers

These service providers will be reset after every request. This option can help developers avoid pollution problems caused by singleton. Such as `auth`.

### message_parser

The websocket message parser. You can replace this option with a custom parser.

> Notice: The custom parser must implement the `HuangYi\Swoole\Contracts\ParserContract`. And the `parse` method must return an object that implements the `HuangYi\Swoole\Contracts\MessageContract`.

### namespace_redis

This option controls the redis connection that store the namespaces of websocket.

Click here for more information about the [namespaces](#Namespaces).

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

Click here for more information about the [tables](#Tables).

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

### Message Route

## Tables

## Task

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
    
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        # IF https
        # proxy_set_header X-Forwarded-Proto https;

        proxy_pass http://127.0.0.1:1215$suffix;
    }
}
```

Websocket:

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
