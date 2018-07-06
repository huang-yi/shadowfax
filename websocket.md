# Websocket

To create a websocket server, you only need to change the `driver` option to "websocket". And the websocket server can also act as a HTTP server.

> Notice: The websocket can only run in Laravel framework. And ensure the value of the `task_worker_num` is greater than 0.

## Namespaces

It's like the [Socket.IO](https://socket.io/docs/rooms-and-namespaces/#namespaces). This package allows you to "namespace" your sockets.

This is a useful feature to minimize the number of resources (TCP connections) and at the same time separate concerns within your application by introducing separation between communication channels.

You can use the `HuangYi\Swoole\Facades\WebsocketNamespace` facade to control the namespaces:

- `void WebsocketNamespace::getPath(int $socketId)`: Get the path of namespace by socketId.
- `array WebsocketNamespace::getClients(string $path)`: Get all clients in the namespace by path.
- `void WebsocketNamespace::broadcast(string $path, \HuangYi\Swoole\Contracts\MessageContract $message, array|int $excepts = null)`: Broadcast a message by path.
- `void WebsocketNamespace::emit(int $socketId, \HuangYi\Swoole\Contracts\MessageContract $message)`: Emit a message to client.
- `void WebsocketNamespace::flush(string $path)`: Flush namespace by path.
- `void WebsocketNamespace::flushAll()`: Flush all namespaces.

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

## Websocket Route

You can define a namespace using websocket route facade. The websocket router is inherited from the `Illuminate\Routing\Router`.

```php
<?php

use HuangYi\Swoole\Facades\WebsocketNamespace;
use HuangYi\Swoole\Facades\WebsocketRoute;
use HuangYi\Swoole\Websocket\Message\Message;
use Illuminate\Http\Request;

WebsocketRoute::path('/chatting-room', function (Request $request) {
    $socketId = app('swoole.http.request')->fd;
    $path = $request->path();
    $message = Message::make('user join', "User [{$socketId}] joined.");

    WebsocketNamespace::broadcast($path, $message, $socketId);
});

```

## Message Route

The message route is used to specify a handler for a websocket message.

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

All the action method will be injected a message entity: `HuangYi\Swoole\Websocket\Message\Message`.

The default message format is:

```json
{
    "event": "event name",
    "data": {
        "foo": "bar"
    }
}
```

## Nginx configurations

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

        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;

        proxy_pass http://127.0.0.1:1215$suffix;
    }
}
```
