# 协程

- [启用协程](#enable)
- [数据库连接池](#db-pools)
- [Redis连接池](#redis-pools)

协程是Swoole的最强武器，也是实现高并发的精髓所在。Shadowfax提供了一个安全的协程环境，你可以放心地启用协程特性。在协程环境下，
Shadowfax会创建一个Laravel容器池，这样保证每个协程在运行时都有自己的容器，从而避免了多协程共用容器而造成的资源污染问题。

> {primary} Laravel容器池的大小可通过`app_pool_capacity`配置项来控制，数值越大消耗的内存越多

<a name="enable"></a>
## 启用协程

Shadowfax默认是关闭协程的，只需将`server.enable_coroutine`设置为`true`即可开启：

```yaml
server:
  enable_coroutine: true
```

<a name="db-pools"></a>
## 数据库连接池

现代Web应用几乎离不开数据库的使用，在协程环境下使用数据库如果不配合连接池，就会造成连接异常。如果我们在业务代码中使用Swoole的Channel来手动创建
连接池，就需要开发人员自行控制数据库连接的获取和回收，并且还要改掉所有操作数据库的代码，这种体验非常不好。

Shadowfax中使用连接池在代码层面是无感的，你不需要关注连接池本身的操作，依然像往常一样使用Laravel的Model或查询构造器来操作数据库。
你唯一需要做的就是将项目中使用到的数据库连接名配置到`db_pools`当中即可：

```yaml
db_pools:
  mysql: 3
  pgsql: 5
```

上述配置中数字为连接池容量，需要开发者根据实际环境进行调整。

<a name="redis-pools"></a>
## Redis连接池

与数据库连接池类似，你只需要将项目中使用到的Redis连接名配置到`redis_pools`当中即可：

```yaml
redis_pools:
  default: 3
  cache: 5
```
