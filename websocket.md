# Websocket

To create a websocket server, you only need to change the `driver` option to "websocket". And the websocket server can also act as a HTTP server.

> Notice: The websocket can only run in Laravel framework. And ensure the value of the `task_worker_num` is greater than 0.

## Events

The main idea behind this package is that you can send and receive any events you want, with any data you want.

```php
<?php

use HuangYi\Swoole\Facades\WebSocket;
use HuangYi\Swoole\WebSocket\Message;

WebSocket::on('new message', function (Message $message) {
    $to = $message->getData('to');

    WebSocket::emit($to, Message::make('new message', [
        'content' => $message->getData('content'),
    ]));
});

```

- `WebSocket::on()`: Receive event from client.
- `WebSocket::emit()`: Send event to client.

## Rooms

This is a useful feature to minimize the number of resources (TCP connections) and at the same time separate concerns within your application by introducing separation between communication channels.

This package separates rooms through the path of URL. We call the default room '/' and it’s the one WebSocket clients connect to by default, and the one the server listens to by default.

Define rooms using the method `WebSocket::room()`:

```php
<?php

use HuangYi\Swoole\Facades\WebSocket;

// Define a game room.
WebSocket::room('game-room', 'App\WebSocket\GameController@connected')
    ->on('play', 'App\WebSocket\GameController@play')
    ->on('pause', 'App\WebSocket\GameController@pause');

// Define a kind of chat room.
WebSocket::room('chat-rooms/{id}')
    ->on('new message', 'App\WebSocket\ChatController@newMessage');

```

> `HuangYi\Swoole\Facades\WebSocket::room()` is equivalent to `Illuminate\Support\Facades\Route::get()`.

## Broadcasting

```php
<?php

use HuangYi\Swoole\Facades\WebSocket;
use HuangYi\Swoole\WebSocket\Message;

WebSocket::on('user joined', function (Message $message) {
    $user = $message->getSocketId();

    $broadcastMessage = Message::make('user joined', [
        'content' => 'User ['.$user.'] joined.',
    ]);

    WebSocket::broadcast($broadcastMessage, $user);
});

```

However, if you only want to broadcast messages to a room, you need to use the method `HuangYi\Swoole\WebSocket\Room::broadcast()`.

```php
<?php

use HuangYi\Swoole\Facades\WebSocket;
use HuangYi\Swoole\WebSocket\Message;

$message = Message::make('notification', ['content' => 'This is a notification']);

// Get room by path.
$room = WebSocket::getRoom('/chat-rooms/1');

$room->broadcast($message);

// Get room by socket id.
$room = WebSocket::getRoom(1);

$room->broadcast($message);

```

## Message

The class `HuangYi\Swoole\WebSocket\Message` has 3 properties:

- `event`: The event name;
- `data`: The event data;
- `socketId`: The socket id of client.

The default format for WebSocket messages is:

```json
{
    "event": "event name",
    "data": {
        "key": "value"
    }
}
```

> Of course, you can create a message parser to customize the message format. The custom parser must implement the contract `HuangYi\Swoole\Contracts\ParserContract`.

## Client code

```javascript
let ws = new WebSocket('ws://127.0.0.1:1215/chat-room');

// Receive message.
ws.onmessage = function (event) {
    console.log(event.data);
};

// Send message.
let message = JSON.stringify({
    event: 'new message',
    data: {
        content: 'This is a new message.'
    }
});

ws.send(message);

```

## Nginx

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
