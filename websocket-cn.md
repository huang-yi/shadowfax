# Websocket

如果你想创建一个Websocket服务器，只需要将配置文件中的`driver`配置为`websocket`即可。Websocket服务器同时也能作为HTTP服务器。

> 注意：目前WebSocket仅支持在Laravel框架中使用，不支持Lumen。此外消息的发送和广播使用了Task，请确保配置项`task_worker_num`的值大于0。

## 事件（Events）

WebSocket最主要就是帮助你发送或接收各种事件。比如聊天室客户端发送一条消息，服务端推送一个系统通知等等。这里我们主要讨论如监听应客户端的事件，以及如何给客户端发送事件。

看一段演示代码：

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

上述代码中，`WebSocket::on()`表示监听客户端的事件，`WebSocket::emit()`表示给客户端发送事件。
所以上述代码就表示，当服务器监听到事件`new message`后，就给客户端`$to`发送一个`new message`事件，这样就完成了一个聊天室发送新消息的功能了。

## 广播（Broadcasting）

我们可以使用`WebSocket::broadcast()`方法给所有客户端广播消息。

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

上述代码表示，当服务器监听到`user joined`事件后，就广播一条`user joined`消息，告诉所有客户端，有新用户加入了。

`WebSocket::broadcast()`方法接收两个参数，第一个为消息实体，第二参数接收一个或一组客户端的socket_id，这部分客户端将不会收到这条消息。一般来说，发消息的客户端肯定知道自己所发的消息，所以服务器可以不通知它。当然我们也可以利用第二个参数来屏蔽不需要接收这条消息的客户端。

## 房间（Rooms）

房间的意义，在于将客户端划分到不同的区块。比方说你的应用需要一个聊天室和一个游戏室，或者你的应用需要多个聊天室。

我们通过URL的path部分来划分房间，比如下面两个URL一个代表聊天室，一个代表游戏室：

```
wss://example.com/chat-room
```

```
wss://example.com/game-room
```

下列URL通过ID代表不同的聊天室：

```
wss://example.com/chat-rooms/1
```

```
wss://example.com/chat-rooms/2
```

在服务端，我们通过`WebSocket::room()`方法定义房间：

```php
<?php

use HuangYi\Swoole\Facades\WebSocket;

// 定义一个房间
WebSocket::room('chat-room');

// 定义一类房间
WebSocket::room('chat-rooms/{id}');

```

> 系统默认将客户端加入path为`/`的房间。

## Nginx配置

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
