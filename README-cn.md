# Shadowfax

Shadowfax可以使你的Laravel应用运行在[Swoole](https://www.swoole.com/)之上，从而获得大幅的性能提升。

## 安装

你可以使用Composer将Shadowfax安装到你的Laravel项目中:

```sh
composer require huang-yi/shadowfax
```

安装好Shadowfax之后，使用Laravel的Artisan命令发布配置文件:

```
php artisan vendor:publish --provider="HuangYi\Shadowfax\ShadowfaxServiceProvider"
```

## 配置

主配置文件位于项目根目录的`shadowfax.ini`，该文件允许你自定义`swoole-http-server`的各项参数。

1. 基本配置：

- **name**：指定进程名
- **host**：服务器监听的IP地址，默认`127.0.0.1`
- **port**：服务器监听的端口，默认`1215`
- **mode**：服务器模式，支持`base`、`process`两种，默认为`base`
- **access_log**：是否开启访问日志，启用后会打印所有的request，默认开启
- **runtime_hooks**：设置Swoole的Runtime Hooks，默认不启用。`1`表示全部启用，如果你不想全部启用，也可以设置具体的flag值（整型）
- **app_pool_capacity**：设置App池的容量，默认100。仅在启用协程后有效
- **bootstrap**：设置Laravel的启动文件，如果你修改了Laravel框架的文件结构，那么你也需要设置该项配置

2. `server`配置：

`server`即swoole-server的选项，请参考[官方文档](https://wiki.swoole.com/wiki/page/274.html)并结合自己的需求进行调整。

3. `controller`配置：

`controller`是指控制服务器，用于控制Shadowfax的停止与重载，这里可以配置控制服务器的`host`和`port`。

## 命令

安装Shadowfax后，会发布一个脚本位于`vendor/bin/shadowfax`，该脚本提供一系列命令来控制服务器进程的启动、停止与重载。该脚本基于Symfony的Console模块实现，所以可以执行`./vendor/bin/shadowfax list`来获得帮助。

### 启动服务器

你可以执行命令`./vendor/bin/shadowfax start`来启动服务器，默认监听的地址是`127.0.0.1:1215`，你也可以通过指定`--host`和`--port`来修改监听地址。该命令默认读取项目根目录下的`shadowfax.ini`配置文件，当然你可以用`--config`来指定你的配置文件。

该命令该还提供了一个选项`--watch`，可以使你的服务器运行在`watch`模式下。该模式会监控项目下的文件，如果有文件发生了变动，就会自动重载服务器进程。监控规则在`.watch`文件中配置，可自行调整。

> 注意：`watch`模式依赖于[fswatch](https://github.com/emcrisostomo/fswatch)，请提前安装。

### 重载服务器

你可以通过命令`./vendor/bin/shadowfax reload`来重载服务器的进程，如果你在启动服务器时指定了配置文件，那么重载时也需要通过`--config`指定配置文件。如果你只想重载Task进程，请指定选项`--task`。

> 注意：如果你的Server配置的模式为`base`，则不支持重载Task进程。

### 停止服务器

你可以执行命令`./vendor/bin/shadowfax stop`来结束服务器，如果你在启动服务器时指定了配置文件，那么重载时也需要通过`--config`指定配置文件。

## 协程

Shadowfax默认是关闭协程特性的，如需启用请调整配置项`server.enable_coroutine`和`server.task_enable_coroutine`。如果你想启用Swoole的Runtime Hooks，请调整设置`runtime_hooks`的值。

> 需要特别注意的是，目前不建议启用MySQL、Redis等可复用客户端的hook，在未实现连接池的前提下，不同的协程使用同一个客户端会导致异常。

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
     * @return void
     */
    public function handle($server, $taskId, $fromWorkerId)
    {
        // handle your task here.
    }
}

```

然后，可以使用Facade进行任务投递：

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
command=cd /path/to/project && ./vendor/bin/shadowfax start
autostart=true
autorestart=true
user=www
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/supervisor.log
```

## 单元测试

```sh
composer test
```

## 协议

Shadowfax是一个开源软件，遵循[MIT协议](LICENSE)。
