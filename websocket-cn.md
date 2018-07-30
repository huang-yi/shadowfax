# Websocket

如果你想创建一个Websocket服务器，只需要将配置文件中的`driver`配置为`websocket`即可。Websocket服务器同时也能作为HTTP服务器。

> 注意：目前WebSocket仅支持在Laravel框架中使用，不支持Lumen。此外消息的发送和广播使用了Task，请确保配置项`task_worker_num`的值大于0。

## 事件（Events）

WebSocket最主要就是帮助开发者发送或接收各种事件。比如聊天室客户端发送一条消息，服务端推送一个系统通知等等。
这里我们主要讨论如何监听应客户端的事件，以及如何给客户端发送事件。

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
当服务器监听到客户端的`new message`事件后，就给客户端`$to`发送一个`new message`事件，这样就完成了一个聊天室发送新消息的功能了。

`WebSocket::on()`方法的第二个参数既可以是一个闭包，也可以是这种形式：`ChatController@newMessage`，指定一个类的方法作为事件回调，与Laravel的路由风格类似。

事件回调方法会被注入一个[`HuangYi\Swoole\WebSocket\Message`](#message)实例，即客户端发送的消息实体，可以通过该对象获取客户端的传参。

## 房间（Rooms）

房间的意义在于将客户端分割到不同的区块。比方说你的应用需要一个聊天室和一个游戏室，或者你的应用需要多个聊天室，都可以用房间来实现。

我们通过URL的path部分来划分房间。**默认地，系统会将客户端放入path为`/`的房间。**

比如下面两个URL一个代表聊天室，一个代表游戏室：

```
wss://example.com/chat-room
```

```
wss://example.com/game-room
```

也可以利用URL参数定义一类房间：

```
wss://example.com/chat-rooms/1
```

```
wss://example.com/chat-rooms/2
```

我们可以使用`WebSocket::room()`方法创建房间：

```php
<?php

use HuangYi\Swoole\Facades\WebSocket;

// 创建一个房间
WebSocket::room('game-room');

// 创建一类房间
WebSocket::room('chat-rooms/{id}');

```

当然，只创建房间没有意义，我们还需要为房间指定监听事件：

```php
<?php

use HuangYi\Swoole\Facades\WebSocket;

WebSocket::room('game-room', 'App\WebSocket\GameController@connected')
    ->on('play', 'App\WebSocket\GameController@play')
    ->on('pause', 'App\WebSocket\GameController@pause');

WebSocket::room('chat-rooms/{id}')
    ->on('new message', 'App\WebSocket\ChatController@newMessage');

```

`WebSocket::room()`方法第二个参数指定的回调方法会在客户端成功连接服务器后触发，开发者可以利用这个回调做身份认证，存储用户信息等逻辑。

> `WebSocket::room()`的本质就是定义一个HTTP路由。

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

上述代码表示，当服务器监听到`user joined`事件后，就广播一个`user joined`事件，告诉所有客户端有新用户加入了。

`WebSocket::broadcast()`方法接收两个参数，第一个为需要广播的消息实体，第二参数接收一个或一组客户端的socket_id，这部分客户端将不会收到这条消息，所以我们也可以利用第二个参数来屏蔽不需要接收这条消息的客户端。

需要注意的是，`WebSocket::broadcast()`方法会给所有的客户端广播消息，如果我们只想给某个房间的客户端广播消息，需要使用`Room::broadcast()`方法：

```php
<?php

use HuangYi\Swoole\Facades\WebSocket;
use HuangYi\Swoole\WebSocket\Message;

$message = Message::make('notification', ['content' => 'This is a notification']);

// 已知path，可以通过WebSocket::getRoom()方法获取Room对象
$path = '/chat-rooms/1';

WebSocket::getRoom($path)->broadcast($message);

// 已知socket_id，可以通过WebSocket::getClientRoom()方法获取Room对象
$socketId = 1;

WebSocket::getClientRoom($socketId)->broadcast($message);

```

## Message

不论是接收还是发送事件，都是由`HuangYi\Swoole\WebSocket\Message`对象传递的。该类有3个成员属性：

- `event`，表示接收或发送的事件名；
- `data`，表示接收或发送的事件参数；
- `socketId`，表示接收到事件的客户端socket_id（服务端发送事件时该参数无意义）；

默认地，WebSocket的消息格式为：

```json
{
    "event": "event name",
    "data": {
        "key": "value"
    }
}
```

其中`data`项可选，如果不需要传参，则可不传`data`项。

> 当然，如果开发者想自定义消息格式，只需要自行实现一个Parser即可，自定义Parser必须实现`HuangYi\Swoole\Contracts\ParserContract`合约，并在配置文件`config/swoole.php`中修改`message_parser`选项。

## Nginx配置

如果开发者想为自己的WebSocket服务配置域名，可以使用nginx的方向代理：

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
