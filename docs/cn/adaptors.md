# 适配器

- [encore/laravel-admin](#encore-laravel-admin)
- [tymon/jwt-auth](#tymon-jwt-auth)

许多拓展包在Swoole环境下运行会有问题，这里收集了各种拓展包的适配方法。

<a name="encore-laravel-admin"></a>
## [encore/laravel-admin](https://github.com/z-song/laravel-admin)

先创建一个EncoreLaravelAdminCleaner：

```shell
php artisan shadowfax:cleaner EncoreLaravelAdminCleaner
```

然后复制下面的代码到`app/Cleaners/EncoreLaravelAdminCleaner.php`：

```php
<?php

namespace App\Cleaners;

use Encore\Admin\Admin;
use HuangYi\Shadowfax\Contracts\Cleaner;
use Illuminate\Contracts\Container\Container;
use ReflectionClass;

class EncoreLaravelAdminCleaner implements Cleaner
{
    /**
     * The static properties should be reset.
     *
     * @var array
     */
    protected $staticProperties = [
        'deferredScript' => [],
        'script'         => [],
        'style'          => [],
        'css'            => [],
        'js'             => [],
        'html'           => [],
        'headerJs'       => [],
        'manifestData'   => [],
        'extensions'     => [],
        'minifyIgnores'  => [],
    ];

    /**
     * Clean "encore/laravel-admin" package.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function clean(Container $app)
    {
        $admin = new ReflectionClass(Admin::class);

        foreach ($this->staticProperties as $name => $value) {
            if ($admin->hasProperty($name)) {
                $admin->setStaticPropertyValue($name, $value);
            }
        }
    }
}
```

<a name="tymon-jwt-auth"></a>
## [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth)

先创建一个TymonJwtAuthCleaner：

```shell
php artisan shadowfax:cleaner TymonJwtAuthCleaner
```

然后复制下面的代码到`app/Cleaners/TymonJwtAuthCleaner.php`：

```php
namespace App\Cleaners;

use HuangYi\Shadowfax\Contracts\Cleaner;
use Illuminate\Contracts\Container\Container;
use Laravel\Lumen\Application as Lumen;
use ReflectionObject;
use Tymon\JWTAuth\Providers\LaravelServiceProvider;
use Tymon\JWTAuth\Providers\LumenServiceProvider;

class TymonJwtAuthCleaner implements Cleaner
{
    /**
     * Clean the "tymon/jwt-auth" package.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function clean(Container $app)
    {
        $class = $app instanceof Lumen ? LumenServiceProvider::class : LaravelServiceProvider::class;

        $provider = new $class($app);

        $method = (new ReflectionObject($provider))->getMethod('extendAuthGuard');

        $method->setAccessible(true);

        $method->invoke($provider);
    }
}
```

最后在配置项中添加下列`abstracts`：

```yaml
abstracts:
  - tymon.jwt.provider.auth
  - tymon.jwt.provider.storage
  - tymon.jwt.manager
  - tymon.jwt
  - tymon.jwt.auth
  - tymon.jwt.blacklist
```
