# 安装

- [Swoole拓展](#ext-swoole)
- [Composer安装](#composer)
- [发布资源](#publish-assets)

<a name="ext-swoole"></a>
## Swoole拓展

Shadowfax依赖于Swoole拓展，所以必须提前安装，可使用`pecl`来快速安装：

```shell
pecl install swoole
```

如果使用编译安装，请尽量选择最新稳定版，对Swoole的最小支持版本是`4.3.2`。

<a name="composer"></a>
## Composer安装

请使用Composer安装Shadowfax到你的Laravel项目中：

```shell
composer require huang-yi/shadowfax
```

如果你正在使用Lumen，安装完后还需要手动注册Shadowfax服务：

```php
$app->register(HuangYi\Shadowfax\ShadowfaxServiceProvider::class);
```

<a name="publish-assets"></a>
## 发布资源

安装之后，请使用Artisan命令`shadowfax:publish`来发布Shadowfax的资源:

```shell
php artisan shadowfax:publish
```

发布的资源内容有：

```shell
ProjectRoot/
 │
 ├── bootstrap/
 │   │
 │   └── shadowfax.php
 │
 ├── .watch
 │
 ├── shadowfax
 │
 ├── shadowfax.yml
 │
 └── shadowfax.yml.example
```

1. `bootstrap/shadowfax.php`类似于`bootstrap/app.php`，该文件用于启动Shadowfax，并且方便开发者注入一些自定义逻辑
2. `.watch`文件用于定义`fswatch`的监控规则
3. `shadowfax`与`artisan`类似，是一个Console应用，为Shadowfax服务器提供了便利的管理命令
4. `shadowfax.yml`文件为Shadowfax的主配置文件，会被自动添加到`.gitignore`中
5. `shadowfax.yml.example`为配置示例文件，可以通过复制该文件来手动创建`shadowfax.yml`
