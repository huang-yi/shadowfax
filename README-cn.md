[English](README.md) | 中文

# Shadowfax

Shadowfax可以使你的Laravel应用运行在[Swoole](https://www.swoole.com/)之上，从而获得大幅的性能提升。

## 安装

你可以使用Composer将Shadowfax安装到你的Laravel项目中:

```shell
composer require huang-yi/shadowfax
```

如果你在使用Lumen框架，你需要手动注册服务到`bootstrap/app.php`文件中去:

```php
$app->register(HuangYi\Shadowfax\ShadowfaxServiceProvider::class);
```

安装好之后，请用Artisan命令`shadowfax:publish`来发布配置文件:

```shell
php artisan shadowfax:publish
```

## 配置

主配置文件为`shadowfax.yml`，位于项目根目录，并且这个文件会被`shadowfax:publish`命令添加到`.gitignore`中。在其它环境中，可以自行拷贝`shadowfax.yml.example`文件进行配置。

1. 基本配置：

- **name**：指定进程名
- **type**: 服务器类型：`http`, `websocket`
- **host**：服务器监听的IP地址
- **port**：服务器监听的端口
- **mode**：服务器模式，支持`base`、`process`两种
- **access_log**：是否开启访问日志，启用后会打印所有的request
- **app_pool_capacity**：设置App池的容量，仅在启用协程后有效
- **framework_bootstrapper**：设置Laravel的启动文件，如果你修改了Laravel框架的文件结构，那么你也需要设置该项配置

2. `server`配置：

`server`即`Swoole\Server`的选项，请参考[官方文档](https://wiki.swoole.com/wiki/page/274.html)并结合自己的需求进行调整。

3. `abstracts`配置:

这个配置项可以设置一组绑定在Laravel IoC容器中的abstracts，每个request结束后这些abstracts会被重新绑定。

4. `websocket`配置:

- **message**: 指定消息实体类名，这个类必须实现`HuangYi\Shadowfax\Contracts\WebSocket\Message`接口.

3. `controller`配置：

`controller`是指控制服务器，用于控制Shadowfax的停止与重载，这里可以配置控制服务器的`host`和`port`。

## 命令

安装Shadowfax后，会发布一个`shadowfax`脚本位于项目根目录，该脚本提供一系列命令来控制服务器进程的启动、停止与重载。该脚本基于Symfony的Console模块实现，所以可以执行`php shadowfax list`来获得帮助。

### 启动服务器

你可以执行命令`php shadowfax start`来启动服务器，默认监听的地址是`127.0.0.1:1215`，你也可以通过指定`--host|-h`和`--port|-p`来修改监听地址。

该命令该还提供了一个选项`--watch|-w`，可以使你的服务器运行在`watch`模式下。该模式会监控项目下的文件，如果有文件发生了变动，就会自动重载服务器进程。监控规则在`.watch`文件中配置，可自行调整。

如果你不想使用默认的配置文件`shadowfax.yml`，也可以使用`--config|-c`来指定一个配置文件。

> 注意：`watch`模式依赖于[fswatch](https://github.com/emcrisostomo/fswatch)，请提前安装。

### 重载服务器

你可以通过命令`php shadowfax reload`来重载服务器的进程，如果你只想重载Task进程，请指定选项`--task|-t`。

> 注意：如果你的Server配置的模式为`base`，则不支持重载Task进程。如果运行`start`命令时指定了配置文件，这里也需要指定配置文件

### 停止服务器

你可以执行命令`php shadowfax stop`来结束服务器。

> 注意：如果运行`start`命令时指定了配置文件，这里也需要指定配置文件

## 协程

Shadowfax默认是关闭协程特性的，如需启用请调整配置项`server.enable_coroutine`和`server.task_enable_coroutine`。

> 注意：如果启用了`一键协程化`或者配置了`hook_flags`，请注意`app_pool_capacity * server.worker_num`的值不能超过数据库的最大连接数，否则可能因为连接数过多而导致报错。

## WebSocket服务器

你也可以使用Shadowfax来构建你的WebSocket服务。

首先，你需要将配置项`type`的值修改为`websocket`。然后创建一个实现了`HuangYi\Shadowfax\Contracts\WebSocket\Handler`接口的handler类，这个类用于处理你的服务端逻辑，例如：

```php
<?php
namespace App\WebSocket;

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
        $connection->send(['status' => 'connected']);
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
        $connection->send(['status' => 'closed']);
    }
}

```

并在你的路由文件中将这个handler绑定到一个uri上面：

```php
<?php

use App\WebSocket\EchoServer;
use HuangYi\Shadowfax\Facades\WebSocket;

WebSocket::listen('/echo', new EchoServer);

```

现在你就可以通过命令`php shadowfax start`来启动你的WebSocket服务了。

## Task

Task功能需要启用Swoole的task进程，所以需要将配置项`server.task_work_num`设置为大于0的数值。启用task进程后，某些耗时任务就可以投递到task进程中进行异步处理。

首先在程序中创建一个task类，所有task类必须实现`HuangYi\Shadowfax\Contracts\Task`接口：

```php
<?php

namespace App\Tasks;

use App\User;
use HuangYi\Shadowfax\Contracts\Task;
use Illuminate\Queue\SerializesModels;

class MyTask implements Task
{
    use SerializesModels;

    /**
     * The user model.
     *
     * @var \App\User 
     */
    public $user;

    /**
     * MyTask constructor.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Handle the task.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $taskId
     * @param  int  $fromWorkerId
     * @param  int  $flags
     * @return void
     */
    public function handle($server, $taskId, $fromWorkerId, $flags)
    {
        // handle your task here.
    }
}

```

然后使用Shadowfax提供的Facade进行任务投递：

```php
<?php

use App\Tasks\MyTask;
use HuangYi\Shadowfax\Facades\Task;

$task = new MyTask(auth()->user());

Task::dispatch($task);

```

## Nginx配置

在生产环境中，你可以使用Nginx作为反向代理服务器：

```nginx
# Uncomment this if you are running a websocket server.
# map $http_upgrade $connection_upgrade {
#     default upgrade;
#     '' close;
# }

server {
    listen 80;
    server_name example.com;
    root /example.com/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location = /index.php {
        try_files /nonexistent_file @shadowfax;
    }

    location / {
        try_files $uri $uri/ @shadowfax;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 @shadowfax;

    location @shadowfax {
        set $suffix "";

        if ($uri = /index.php) {
            set $suffix ?$query_string;
        }

        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Port $server_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        # Uncomment this if you are running a websocket server.
        # proxy_set_header Upgrade $http_upgrade;
        # proxy_set_header Connection $connection_upgrade;

        proxy_pass http://127.0.0.1:1215$suffix;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

> 请将Shadowfax监听的IP填加到`App\Http\Middleware\TrustProxies`中，这样才能在你的Laravel程序中获得正确的IP。

## Supervisor配置

在生产环境中，可以使用Supervisor来管理你的Shadowfax进程：

```ini
[program:shadowfax]
process_name=%(program_name)s
directory=/path/to/project
command=php shadowfax start
autostart=true
autorestart=true
user=www
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/supervisor.log
```

## Benchmarks

我们使用开源软件[wrk](https://github.com/wg/wrk)进行压力测试。

### 环境1

- 硬件: 1 CPU, 4 Cores, 16GB Memory
- MacOS 10.15.3
- PHP 7.3.12（启用opcache）
- Swoole 4.4.13
- Laravel 7（无session中间件）
- Shadowfax 2.0.0（20个worker进程）

wrk启动4个线程，并发200进行压测：

```shell
wrk -t4 -c200 http://127.0.0.1:1215/
```

结果为**12430.20rps**：

```shell
Running 10s test @ http://127.0.0.1:1215/
  4 threads and 200 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    26.44ms   31.44ms 212.73ms   84.28%
    Req/Sec     3.13k   839.99     6.07k    65.75%
  124418 requests in 10.01s, 312.06MB read
  Socket errors: connect 0, read 54, write 0, timeout 0
Requests/sec:  12430.20
Transfer/sec:     31.18MB
```

### 环境2

- 硬件: 2 CPUs, 2 Cores, 4GB Memory
- CentOS 7.5.1804
- PHP 7.3.16（启用opcache）
- Swoole 4.4.17
- Laravel 7（无session中间件）
- Shadowfax 2.0.0（10个worker进程）

wrk启动2个线程，并发100进行压测：

```shell
$ wrk -c100 http://127.0.0.1:1215/
```

结果为**4001.76rps**：

```shell
Running 10s test @ http://127.0.0.1:1215/
  2 threads and 100 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    25.06ms   12.11ms  85.92ms   60.94%
    Req/Sec     4.02k    41.46     4.08k    79.79%
  40321 requests in 10.08s, 101.13MB read
Requests/sec:   4001.76
Transfer/sec:     10.04MB
```

## 单元测试

```shell
composer test
```

## 协议

Shadowfax是一个开源软件，遵循[MIT协议](LICENSE)。
