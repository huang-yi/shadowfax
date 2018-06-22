# Laravel-Swoole-Http

一个基于[Swoole](http://www.swoole.com/)的高性能HTTP Server，帮助你大幅度地提高网站的并发能力。

当然，现在它也支持[Websocket Server](#Websocket)。

## 安装

1、在安装Package之前，请确认自己的环境是否满足条件：

| Laravel | Lumen | Swoole  |
|:-------:|:-----:|:-------:|
| ~5.2    | ~5.2  | >=1.9.5 |

2、请根据需求，确认以下PHP拓展是否已安装：

| 拓展名 | 必选 | 说明 |
|:-----:|:---:|:---:|
| swoole | true | 该package基于swoole开发，所以此拓展必须安装 |
| inotify | false | 该package为开发者提供了`watch`模式，如果不需要此功能，可不必安装 |

> 注意：PHP拓展可以选择编译安装，或者使用`pecl`命令快速安装，例如`pecl install swoole`。PHP拓展安装完成后需要在`php.ini`中添加配置。

3、然后使用composer安装package：

```
$ composer require huang-yi/laravel-swoole-http
```

## 快速使用

**1、注册服务（可选）**

如果你正在使用低版本的Laravel框架（小于5.5），请在`config/app.php`的providers数组中手动添加服务提供器：

```php
[
    'providers' => [
        HuangYi\Swoole\SwooleServiceProvider::class,
    ],
]
```

如果你正在使用低版本的Lumen框架（小于5.5），将下面这行代码添加到`bootstrap/app.php`文件：

```php
$app->register(HuangYi\Swoole\SwooleServiceProvider::class);
```

5.5及其以后的版本不需要执行上述步骤。

**2、 修改配置**

请运行以下命令快速发布配置文件，它将生成一个`swoole.php`在`config/`文件夹下：

```
$ php artisan vendor:publish  --provider="HuangYi\Swoole\SwooleServiceProvider"
```

请参考[配置说明](#configurations)，调整配置文件参数。

**3、启动服务**

可以使用`php artisan swoole:server`来管理服务，[这里](#commands)可以获取更多关于该命令的说明。

在这里，我们只需要简单执行`php artisan swoole:server`即可快速启动服务。

**4、预览效果**

默认地，我们可以使用浏览器访问地址`http://127.0.0.1:1215`，就可以成功看到Web界面了。

如果你想为自己的网站配置域名，请参考[Nginx配置](#nginx)

## Configurations

### driver

服务器驱动，可选项有`http`、`websocket`。你可以根据自己的需求选择驱动，来创建相应的服务器。

### host

指定服务器监听的IP地址，`127.0.0.1`表示监听本机，也可以使用内网地址或外网地址，`0.0.0.0`表示监听所有地址。

### port

指定服务器监听的端口，默认为`1215`，建议使用大于`1024`的端口。

### options

服务器运行时的各项参数，你可根据自己的需求添加或修改配置项，更多细节请阅读[官方文档](https://wiki.swoole.com/wiki/page/274.html)。

> 注意：如果你想启用Websocket Server或者Task进程，那么必须将`task_worker_num`配置为大于0的数值。

### resets

这项配置允许你配置一些需要被重置的Service Provider或者对象实例。重置时间为每个request后。

> 为什么需要这个配置呢？基于swoole的应用与传统的PHP应用不同，swoole会使你的应用常驻内存，每次request后并不会像PHP-FPM那样销毁所有变量，所以Laravel IoC容器中的instances都会被保存下来。
  例如`auth`组件就是一个典型的单例，在swoole环境下，如果不重置`auth`组件，会导致后续request的用户身份一直为第一个request的用户身份。（注意：并非所有的单例都会带来污染问题，请分析具体的使用场景）。
  因此为了避免单例带来的污染问题，你可以通过该配置项来解决。

### message_parser

Websocket消息的解析器。默认为`HuangYi\Swoole\Websocket\Message\JsonParser::class`。

开发者可以利用该配置项替换为自己的消息解析器。

> 注意：消息解析器必须实现`HuangYi\Swoole\Contracts\ParserContract`合约，且`parse`方法返回的消息实体必须实现`HuangYi\Swoole\Contracts\MessageContract`合约。

### namespace_redis

Websocket的namespace数据使用redis存储，你可以指定一个redis连接来专门管理你的namespace数据。[这里](#namespaces)可以获取更多关于namespace的说明。

### tables

定义Swoole Table的表结构。由于进程间的内存是相互隔离的，如果想进程间共享数据，需要通过Swoole Table。

例如定义一张`users`表：

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

其中`name`为表名；`size`为表格最大行数，其值必须为2的次方；`columns`为表的列，每个列需要定义三个属性：列名、类型、长度。其中类型可以为`int`、`integer`、`string`、`varchar`、`char`、`float`。

[这里](#tables)可以获取更多关于Tables的说明。

> 注意：`int`、`integer`都表示整型，`string`、`varchar`、`char`都表示字符串，没有什么区别。

### watcher

File watcher配置。当使用`php artisan swoole:server watch`运行服务时，会进入watch模式。

`directories`：被监控的目录列表；

`excluded_directories`：不被监控的目录列表；

`suffixes`：文件后缀列表，只有符合后缀条件的文件才会被监控；

## Commands

该package为开发者提供了便捷的Artisan命令来管理服务：`php artisan swoole:server`。该命令接收一个`action`参数：

| Action | 说明 |
|:------:|:---:|
| start | 启动服务，该值为默认值，可缺省 |
| stop | 停止服务 |
| reload | 重载服务。此命令可以帮你平滑地重启服务器 |
| restart | 重启服务 |
| watch | watch模式。当监控到文件发生变动时，服务会自动重载，这样就省去了手动重载的麻烦，让你拥有更愉悦的开发体验。该模式必须安装`inotify`拓展，建议仅在开发环境下使用。 |

> 注意：Swoole Server只能在cli模式下运行。

## Websocket

如果你想创建一个Websocket服务器，只需要将配置文件中的`driver`配置为`websocket`即可。Websocket服务器同时也能作为HTTP服务器。

> 注意：目前Websocket仅支持在Laravel框架中使用，不支持Lumen。此外消息的发送和广播使用了Task，请确保配置项`task_worker_num`的值大于0。

这里有三个概念需要开发者了解：

### Namespaces

与[socket.io](https://socket.io/docs/rooms-and-namespaces/#namespaces)的namespace类似，这是一个十分有用的特性，开发者可以利用path来建立不同的namespace，从而达到分隔信道和应用的效果。

客户端可以通过path加入指定的namespace，服务器会在完成握手协议后自动将客户端的socketId加入namespace，开发者不需要关系其处理细节。
其中socketId和namespace的关系通过redis存储，开发者可以通过配置文件的`namespace_redis`选项指定一个专门的redis连接来管理你的namespace数据。

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

### Websocket Route

服务器端可通过Websocket Route来定义namespace，Websocket Route继承自`Illuminate\Routing\Router`，所以我们依然可以使用其分组、中间件等特性。

开发者可以使用Facade`HuangYi\Swoole\Facades\WebsocketRoute`来定义namespace：

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

> 注意：Websocket Route的中间件组需要用`WebsocketRoute::middlewareGroup()`定义，别名需要用`WebsocketRoute::aliasMiddleware()`定义。

### Message Route

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

## Tables

由于进程间的内存是相互隔离的，我们可以借助Swoole Table实现进程间的共享数据。

开发者可以通过配置文件中的`tables`选项定义表结构，定义好的tables会在服务启动时创建好，我们可以使用Facade`HuangYi\Swoole\Facades\Table`来方便地操作：

```php
<?php
use HuangYi\Swoole\Facades\Table;

// 插入一条数据
Table::use('users')->set(1, ['id' => 1, 'nickname' => 'Bob', 'score' => 9.5]);

// 查询数据
$bob = Table::use('users')->get(1);
$nickname = Table::use('users')->get(1, 'nickname');

// 清空表
Table::truncate('users');

```

更多关于Swoole Table的操作方法，可以查看[官方文档](https://wiki.swoole.com/wiki/page/p-table.html)。

> 注意：Swoole Table必须在Swoole Server启动之前创建好，所以请不要在应用程序中创建Swoole Table。

## Task

在Swoole中，Task是异步非阻塞的。如果开发者遇到一些耗时的工作，我们可以创建一个Task，将其投递到task worker进程中进行异步处理。

新建的Task必须实现`HuangYi\Swoole\Contracts\TaskContract`合约：

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

投递任务：

```php
<?php

$task = new SendMailTask([
    'to' => 'bob@mail.com',
    'view' => 'mail',
    'data' => [],
]);

app('swoole.server')->task($task);

```

> 注意：启用Task进程，必须将配置项`swoole.options.task_worker_num`配置为大于0的数值。

## Nginx

由于Swoole对HTTP协议的支持并不完整，建议仅作为应用服务器，开发者需要使用Nginx反向代理。

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

如果使用Websocket服务器：

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

## 编程须知

- 这些函数不应该出现在程序中（Artisan Command除外）：`sleep()`、`exit()`、`die()`。
- 谨慎使用单例。
