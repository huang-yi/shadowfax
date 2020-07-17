# 配置

- [Name](#name)
- [Type](#type)
- [Host](#host)
- [Port](#port)
- [Mode](#mode)
- [Access Log](#access-log)
- [App Pool Capacity](#app-pool-capacity)
- [Server](#server)
- [Abstracts](#abstracts)
- [Controllers](#controllers)
- [Cleaners](#cleaners)
- [DB Pools](#db-pools)
- [Redis Polls](#redis-polls)
- [Controller Server](#controller-server)

Shadowfax的配置文件是位于根目录的`shadowfax.yml`，如果你不熟悉YAML语法，需要提前了解一下。

<a name="name"></a>
## Name

设置Shadowfax应用的名称，启动的Shadowfax进程将使用该配置来命名（MacOS下无效）。

<a name="type"></a>
## Type

设置Shadowfax服务器类型，支持`http`和`websocket`。

- 设置为`http`时将启动一个HTTP服务器；
- 设置为`websocket`时将启动一个WebSocket服务器。需要注意的是，WebSocket服务器同时也支持HTTP服务器。

<a name="host"></a>
## Host

设置Shadowfax服务器监听的IP地址，默认为`127.0.0.1`。该项配置为`Swoole\Server`构造函数的第一个参数。

<a name="port"></a>
## Port

设置Shadowfax服务器监听的端口，默认为`1215`。该项配置为`Swoole\Server`构造函数的第二个参数。

<a name="mode"></a>
## Mode

设置Shadowfax服务器的模式，支持`process`和`base`，默认为`process`。该项配置为`Swoole\Server`构造函数的第三个参数。

<a name="access-log"></a>
## Access Log

访问日志的开关，默认开启，关闭后将不会在控制台上打印HTTP访问日志。

<a name="app-pool-capacity"></a>
## App Pool Capacity

设置Laravel Application池的容量，仅在启用Swoole的协程特性后有效。

<a name="server"></a>
## Server

设置Shadowfax服务器的运行时的参数，该配置为映射类型。在启动服务器时，该配置的所有选项将被传入`Swoole\Server::set()`方法。默认为：

```yaml
server:
  worker_num: 1
  enable_coroutine: false
```

> {primary} 其中`hook_flags`可设置为`SWOOLE_HOOK_ALL`，该值会被自动转换为`SWOOLE_HOOK_ALL`常量，便于启用一键协程化。

<a name="abstracts"></a>
## Abstracts

设置需要被重置的对象列表，这里只能是被注册到Laravel容器里面的对象。该配置为数组类型，数组内的所有对象将在请求结束时被重置，这能帮助消除上一个请求对下一个请求造成的资源污染。默认值为：

```yaml
abstracts:
  - cookie
  - session
  - session.store
  - redirect
  - auth
  - auth.driver
  - Illuminate\Session\Middleware\StartSession
```

<a name="controllers"></a>
## Controllers

设置需要被清理的控制器类名列表，该数组内的控制器实例将会在请求结束时从Route中清理掉。有两种配置方式：

1. 清除所有控制器（默认）：

```yaml
controllers:
  - "*"
```

2. 指定需要被清除的控制器：

```yaml
controllers:
  - App\Http\Controllers\FooController
  - App\Http\Controllers\BarController
```

<a name="cleaners"></a>
## Cleaners

设置`Cleaner`列表。该配置项为数组类型，可帮助开发者注册自定义的`Cleaner`，所有的`Cleaner`将在请求结束时运行。
该配置项除了可以添加Cleaner类名，也可以添加目录，Shadowfax会自动加载目录下所有的Cleaner类。
需要注意的是，只支持配置`app/`目录下的目录，并且必须遵循PSR4规范。

例如：

```yaml
cleaners:
  - app/Cleaners/
  - app/OtherDir/
  - CustomNamespace\FooCleaner
  - CustomNamespace\BarCleaner
```

<a name="db-pools"></a>
## DB Pools

设置数据库连接池。该配置项为映射类型，其中键名为数据库连接名，键值为连接池容量。Shadowfax将在Worker进程启动时为该配置项中的数据库连接创建连接池，例如：

```yaml
db_pools:
  mysql: 3
  pgsql: 5
```

> {primary} 连接池功能必须启用Swoole的协程特性（即`server.enable_coroutine`设置为`true`），否则即使配置了，也不会创建连接池。

<a name="redis-pools"></a>
## Redis Pools

设置Redis连接池。该配置项为映射类型，其中键名为Redis连接名，键值为连接池容量。Shadowfax将在Worker进程启动时为该配置项中的Redis连接创建连接池，例如：

```yaml
redis_pools:
  default: 3
  cache: 5
```

<a name="controller-server"></a>
## Controller Server

设置Shadowfax的Controller服务器配置，Controller服务器可以`stop|reload`Shadowfax服务器：

```yaml
controller:
  host: 127.0.0.1
  port: 1216
```
