# 简介

- [特性](#features)
- [Benchmarks](#benchmarks)
- [贡献](#contributes)
- [协议](#license)

Shadowfax是一个Laravel拓展包，它可以让你的Laravel应用运行在[Swoole](https://www.swoole.com/)之上，以获得巨大的性能提升。

<a name="features"></a>
## 特性

- 不破坏Laravel的开发体验，让Laravel程序在Swoole与PHP-FPM上都能运行
- 可放心地启用协程特性
- 无感知地使用数据库/Redis连接池

如果想深入了解Shadowfax，可以阅读作者的这篇文章：[《整合Laravel与Swoole，Shadowfax是这样做的》](https://huangyi.tech/posts/how-did-shadowfax-integrate-laravel-with-swoole)。

<a name="benchmarks"></a>
## Benchmarks

使用开源软件[wrk](https://github.com/wg/wrk)进行压力测试。

### 环境1

- 硬件: 1 CPU, 4 Cores, 16GB Memory
- MacOS 10.15.3
- PHP 7.3.12（启用opcache）
- Swoole 4.4.13
- Laravel 7.x（无session中间件）
- Shadowfax 2.x（20个worker进程）

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
- Laravel 7.x（无session中间件）
- Shadowfax 2.x（10个worker进程）

wrk启动2个线程，并发100进行压测：

```shell
wrk -c100 http://127.0.0.1:1215/
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

<a name="contributes"></a>
## 贡献

Shadowfax是一个开源软件，代码托管在Github之上，项目地址为：[https://github.com/huang-yi/shadowfax](https://github.com/huang-yi/shadowfax)。

你可以通过以下方式来贡献你的力量：

- 如果觉得Shadowfax好用，请前往[项目主页](https://github.com/huang-yi/shadowfax)贡献一个Star
- 如果你在使用的过程中遇到了BUG，请在[这里](https://github.com/huang-yi/shadowfax/issues)提交Issue
- 如果你可以改进程序，请在[这里](https://github.com/huang-yi/shadowfax/pulls)提交PR

<a name="license"></a>
## 协议

Shadowfax遵循[MIT](https://github.com/huang-yi/shadowfax/blob/master/LICENSE)开源协议。
