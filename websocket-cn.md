# Websocket

如果你想创建一个Websocket服务器，只需要将配置文件中的`driver`配置为`websocket`即可。Websocket服务器同时也能作为HTTP服务器。

> 注意：目前Websocket仅支持在Laravel框架中使用，不支持Lumen。此外消息的发送和广播使用了Task，请确保配置项`task_worker_num`的值大于0。

这里有三个概念需要开发者了解：

## Namespaces

与[socket.io](https://socket.io/docs/rooms-and-namespaces/#namespaces)的namespace类似，这是一个十分有用的特性，开发者可以利用path来建立不同的namespace，从而达到分隔信道和应用的效果。

客户端可以通过path加入指定的namespace，服务器会在完成握手协议后自动将客户端的socketId加入namespace，开发者不需要关心其处理细节。
其中socketId和namespace的关系通过redis存储，开发者可以通过配置文件的`redis_connection`选项指定一个专门的redis连接来管理你的namespace数据。

该Package还提供了一个Facade用于namespace操作：`HuangYi\Swoole\Facades\WebsocketNamespace`。

- `void WebsocketNamespace::getPath(int $socketId)`，通过socketId获取path；
- `array WebsocketNamespace::getClients(string $path)`，通过path获取namespace下所有的连接；
- `void WebsocketNamespace::broadcast(string $path, \HuangYi\Swoole\Contracts\MessageContract $message, array|int $excepts = null)`，通过path广播消息；
- `void WebsocketNamespace::emit(int $socketId, \HuangYi\Swoole\Contracts\MessageContract $message)`，给指定的socketId发送消息；
- `void WebsocketNamespace::flush(string $path)`，通过path清理namespace数据；
- `void WebsocketNamespace::flushAll()`，清理所有的namespace数据；

下面为一个聊天室的例子：

```php
<?php

use HuangYi\Swoole\Facades\WebsocketNamespace;
use HuangYi\Swoole\Websocket\Message\Message;

class ChattingRoom
{
    /**
     * 发送群聊消息
     * 
     * @param \HuangYi\Swoole\Websocket\Message\Message $message
     * @return void
     */
    public function sendGroupMessage(Message $message)
    {
        $socketId = $message->getSocketId();

        // 通过socketId获取path
        $path = WebsocketNamespace::getPath($socketId);

        $broadcastMessage = Message::make('send group message', $message->getData('content'));

        // 通过path广播消息，第三个参数可以设置不发送对象
        WebsocketNamespace::broadcast($path, $broadcastMessage, $socketId);
    }

    /**
     * 发送群聊消息
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

        // 给指定的socketId发送消息
        WebsocketNamespace::emit($to, $privateMessage);
    }
}

```

## Websocket Route

服务器端可通过Websocket Route来定义namespace，Websocket Route继承自`Illuminate\Routing\Router`，所以我们依然可以使用其分组、中间件等特性。

开发者可以使用Facade`HuangYi\Swoole\Facades\WebsocketRoute`来定义namespace：

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

> 注意：Websocket Route的中间件组需要用`WebsocketRoute::middlewareGroup()`定义，别名需要用`WebsocketRoute::aliasMiddleware()`定义。

## Message Route

从客户端发过来的消息，我们可以通过Message Route分发到相应的处理程序。
Message Route可以使用Facade`HuangYi\Swoole\Facades\MessageRoute`定义：

```php
<?php
use HuangYi\Swoole\Facades\MessageRoute;
use HuangYi\Swoole\Websocket\Message\Message;

// 使用闭包处理程序逻辑
MessageRoute::on('send private message', function (Message $message) {
    // Do something.
});

// 使用Controller处理程序逻辑
MessageRoute::on('send group message', 'ChattingRoom@sendGroupMessage');

```

消息对应的处理方法都会被注入一个`HuangYi\Swoole\Websocket\Message\Message`参数，即客户端发过来的消息实体。

默认的消息格式为：

```json
{
    "event": "event name",
    "data": {
        "foo": "bar"
    }
}
```

如果你想自己定义消息格式，可以通过自定义消息解析器实现。自定义消息解析器必须实现`HuangYi\Swoole\Contracts\ParserContract`合约，并且需要将自定义的消息解析器类名配置到配置文件中的`message_parser`选项。

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
