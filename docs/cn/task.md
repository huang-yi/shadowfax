# 任务

- [启动Task Worker进程](#start)
- [重载Task Worker进程](#reload)
- [创建任务](#create)
- [投递任务](#dispatch)

如果你想使用Swoole的Task Worker进程来异步处理一些耗时任务，Shadowfax为你提供了便捷的接口。

<a name="start"></a>
## 启动Task Worker进程

首先你需要启动Swoole的Task Worker进程，只需要在配置文件`shadowfax.yml`的`server`选项配置Task Worker的进程数即可。当然，你也可以通过`task_enable_coroutine`选项来启用协程：

```yaml
server:
  - task_worker_num: 5
  - task_enable_coroutine: true
```

当你使用命令`php shadowfax start`启动服务时，Swoole就会自动开启指定数量的Task Worker进程帮你处理任务。

<a name="reload"></a>
## 重载Task Worker进程

当你修改了业务代码后，可以使用`php shadowfax reload`来平滑重载Swoole的所有Worker进程，如果你只想重载Task Worker进程，指定选项`--task|-t`即可：

```shell
php shadowfax reload -t
```

> {info} Swoole的`base`模式不支持重载Task Worker进程。

<a name="create"></a>
## 创建任务

可以使用Artisan命令来快速创建一个Task类：

```shell
php artisan shadowfax:task SendSms
```

执行完上述命令后，会在`app/Tasks/`文件夹下创建一个名为SendSms的任务类。你可以通过构造函数给任务传递参数，然后在`handle`方法里面写处理任务的逻辑，比如：

```php
<?php

namespace App\Tasks;

use HuangYi\Shadowfax\Contracts\Task;

class SendSms implements Task
{
    /**
     * The phone number.
     *
     * @var string
     */
    protected $phoneNumber;

    /**
     * The content.
     *
     * @var string
     */
    protected $content;

    /**
     * Create a new task instance.
     *
     * @param  string  $phoneNumber
     * @param  string  $content
     * @return void
     */
    public function __construct(string $phoneNumber, string $content)
    {
        $this->phoneNumber = $phoneNumber;
        $this->content = $content;
    }

    /**
     * Handle the task.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $taskId
     * @param  int  $fromWorkerId
     * @param  int  $flags
     * @return mixed
     */
    public function handle($server, $taskId, $fromWorkerId, $flags)
    {
        app('sms')->send($this->phoneNumber, $this->content);
    }
}
```

`handle`方法的参数是Swoole的`task`事件回调函数的相关参数，具体含义可参考`Swoole`官方文档。

当然，你也可以在任意位置手动创建Task类，需要注意的是，你的Task类必须实现`HuangYi\Shadowfax\Contracts\Task`接口。

<a name="dispatch"></a>
## 投递任务

创建好任务后，就可以在业务代码里面使用`HuangYi\Shadowfax\Facades\Task::dispatch()`进行任务投递，比如发送一条欢迎短信：

```php
<?php

namespace App\Http\Controllers;

use App\Tasks\SendSms;
use HuangYi\Shadowfax\Facades\Task;

class WelcomeController extends Controller
{
    /**
     * 发送欢迎短信
     *
     * @return \Illuminate\Http\Response
     */
    public function send()
    {
        $task = new SendSms(auth()->user()->phone_number, '感谢你的注册');

        Task::dispatch($task);

        return response()->noContent();
    }
}
```

当程序调用`HuangYi\Shadowfax\Facades\Task::dispatch()`后，就会将任务投递到Task Worker中运行。
