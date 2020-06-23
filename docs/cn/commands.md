# 命令

- [启动服务器](#start)
- [重载服务器](#reload)
- [停止服务器](#stop)

Shadowfax提供了Console命令来辅助管理服务器，基于Symfony的Console模块实现，可以执行`php shadowfax list`来查看命令列表。

<a name="start"></a>
## 启动服务器

使用`start`命令可启动服务器：

```shell
php shadowfax start
```

如果你不想使用配置文件里面的`host`或`port`，可通过指定`--host|-h`或`--port|-p`修改监听地址：

```shell
php shadowfax start --host=0.0.0.0 --port=5901
```

如果你不想使用默认的配置文件，也可以使用`--config|-c`选项来指定一个配置文件。

`start`命令还提供了`--watch|-w`选项，它可以使你的服务器运行在Watch模式下。Watch模式会监控项目下的文件，一旦有文件发生变动，就会自动重载服务器。监控规则定义在`.watch`文件中，可自行调整。

> {primary} Watch模式依赖于[`fswatch`](https://github.com/emcrisostomo/fswatch)，必须提前安装。

<a name="reload"></a>
## 重载服务器

使用`reload`命令可以重载服务器的Worker进程：

```shell
php shadowfax reload
```

如果启动服务器时使用`--config|-c`指定了配置文件，那么重载时也需要使用`--config|-c`指定配置文件。

如果你只想重载Task Worker进程，可以指定选项`--task|-t`。

> {primary} 由于Swoole的限制，当你的Server为`base`模式，不支持重载Task Worker进程。

<a name="stop"></a>
## 停止服务器

使用`stop`命令可以停止服务器：

```shell
php shadowfax stop
```

如果启动服务器时使用`--config|-c`指定了配置文件，那么停止时也需要使用`--config|-c`指定配置文件。
