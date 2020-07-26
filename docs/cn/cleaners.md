# 清理器

- [清理Laravel容器](#abstracts)
- [清理控制器](#controllers)
- [自定义Cleaner](#cleaners)

Shadowfax会使Laravel程序常驻内存，那么不可避免的就是资源污染问题。如果不及时清理或者还原被污染的资源，就会对下一个请求造成影响，从而引发数据异常。

<a name="abstracts"></a>
## 清理Laravel容器

Laravel出于对性能的考虑，大量的服务都是以单例的形式注册在IoC容器之中的，而这些单例在常驻内存的程序中很容易引起副作用。
举个简单的例子，Laravel的auth组件就是一个典型的单例服务，在用户完成登录后会将当前的User对象保存在一个成员变量中，
那么下一个请求在调用auth组件时，获得的User对象还是上一个请求保存的，这样就会引起用户身份错乱。清理Laravel容器里面的对象，
我们只需要将其配置到配置文件的`abstracts`数组中即可。默认情况下，Shadowfax会清理以下对象，你可以根据自己项目的实际情况进行调整：

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
## 清理控制器

Laravel在dispatch路由时会将其控制器的实例缓存到当前Route中（闭包类型的除外）。在Swoole环境下，再次访问该路由时就不需要再次实例化控制器了。
这样虽然性能好，但在Swoole环境下容易出问题。比如开发者在Controller中保存了一些数据，或者注入了一些有“副作用”的服务，都有可能对下一个请求造成影响。
所有需要将这些控制器实例从Route中清理掉，以便每次dispatch路由时生成新的控制器实例。

Shadowfax提供了一个`controllers`配置来帮助开发者自定义需要清理的控制器：

```yaml
controllers:
  - App\Http\Controllers\FooController
  - App\Http\Controllers\BarController
```

如果配置为`*`表示清除所有控制器实例（默认）：

```yaml
controllers:
  - "*"
```

<a name="cleaners"></a>
## 自定义Cleaner

如果只清理Laravel容器里面的对象，肯定是不能满足需求的，Shadowfax还可以通过创建Cleaner来自定义清理逻辑。

### 创建Cleaner

可以使用Artisan命令`shadowfax:cleaner`来快速创建一个Cleaner：

```shell
php artisan shadowfax:cleaner FooCleaner
```

上述命令会在`app/Cleaners/`目录下创建一个名为FooCleaner的类，你只需要在其`clean`方法里面写入自定义的清理逻辑即可。
当然，你也可以手动地在任意位置创建Cleaner，不过手动创建的Cleaner类必须实现`HuangYi\Shadowfax\Contracts\Cleaner`接口，例如：

```php
<?php

namespace CustomNamespace;

use HuangYi\Shadowfax\Contracts\Cleaner;
use Illuminate\Contracts\Container\Container;

class FooCleaner implements Cleaner
{
    /**
     * Clean something.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function clean(Container $app)
    {
        // Clean polluted data.
    }
}
```

默认地，Cleaners会在请求结束后执行，如果你想让Cleaner在请求之前执行，需要将其`interface`改成`HuangYi\Shadowfax\Contracts\BeforeCleaner`，比如：

```php
<?php

namespace CustomNamespace;

use HuangYi\Shadowfax\Contracts\BeforeCleaner;
use Illuminate\Contracts\Container\Container;

class FooCleaner implements BeforeCleaner
{
    /**
     * Clean something.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function clean(Container $app)
    {
        // Clean polluted data.
    }
}
```

你也可以使用Artisan命令`shadowfax:cleaner`加上一个`--before|-b`选项来创建一个前置Cleaner：

```shell
php artisan shadowfax:cleaner --before FooCleaner
```

### 注册Cleaner

`cleaners`配置项支持目录，并且默认值为`app/Cleaners/`，如果你是使用`shadowfax:cleaner`命令创建的Cleaner类，就不需要做任何事情。否则需要手动将Cleaner类注册到`cleaners`数组中去：

```yaml
cleaners:
  - app/Cleaners/
  - CustomNamespace\FooCleaner
```

这样，你创建的Cleaner就会被自动调用。
