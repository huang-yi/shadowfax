# WebSocket

- [启用WebSocket](#enable)
- [创建Handler](#handlers)
- [路由](#routes)

Shadowfax可帮助开发者快速搭建WebSocket服务器。

<a name="enable"></a>
## 启用WebSocket

首先，你需要将配置项`type`修改为`websocket`，这样Shadowfax启动时就会创建一个WebSocket服务器：

```yaml
type: websocket
```

<a name="handlers"></a>
## 创建Handler

你可以使用Artisan命令`shadowfax:handler`创建一个Handler来编写WebSocket服务端的逻辑代码：

```shell
php artisan shadowfax:handler EchoHandler
```

上述命令会在`app/WebSocket/Handlers/`目录下创建一个名为EchoHandler的类，当然你也可手动创建Handler，
手动创建的Handler必须实现`HuangYi\Shadowfax\Contracts\WebSocket\Handler`接口，例如：

```php
<?php
namespace App\WebSocket\Handlers;

use Illuminate\Http\Request;
use HuangYi\Shadowfax\Contracts\WebSocket\Connection;
use HuangYi\Shadowfax\Contracts\WebSocket\Handler;
use HuangYi\Shadowfax\Contracts\WebSocket\Message;

class EchoServer implements Handler
{
    /**
     * Handler for open event.
     *
     * @param  \HuangYi\Shadowfax\Contracts\WebSocket\Connection  $connection
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function onOpen(Connection $connection, Request $request)
    {
        $connection->send('connected');
    }

    /**
     * Handler for message event.
     *
     * @param  \HuangYi\Shadowfax\Contracts\WebSocket\Connection  $connection
     * @param  \HuangYi\Shadowfax\Contracts\WebSocket\Message  $message
     * @return mixed
     */
    public function onMessage(Connection $connection, Message $message)
    {
        $connection->send($message->getData());
    }

    /**
     * Handler for close event.
     *
     * @param  \HuangYi\Shadowfax\Contracts\WebSocket\Connection  $connection
     * @return mixed
     */
    public function onClose(Connection $connection)
    {
        $connection->send('closed');
    }
}
```

这个EchoHandler实现了一个简单的Echo服务器，它会直接返回客户端发送过来的数据。

<a name="routes"></a>
## 路由

创建好Handler后，需要使用`HuangYi\Shadowfax\Facades\WebSocket`将其绑定到WebSocket路由上面。你可以在`routes/`文件夹下面创建一个`websocket.php`文件：

```php
<?php

use App\WebSocket\Handlers\EchoServer;
use HuangYi\Shadowfax\Facades\WebSocket;

WebSocket::listen('/echo', new EchoServer);

```

并在`app/Providers/RouteServiceProvide.php`中引入`websocket.php`文件：

```php
if (defined('SHADOWFAX_START')) {
    require base_path('routes/websocket.php');
}
```

现在你就可以通过`php shadowfax start`命令来启动你的WebSocket服务了。

> {primary} WebSocket路由本质上还是一个Http路由，所以也可以使用domain、group、prefix、middleware等特性，不过middleware仅在握手阶段有效。
