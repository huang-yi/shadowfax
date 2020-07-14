English | [中文](README-cn.md)

# Shadowfax

The Shadowfax is a package that runs your Laravel application on [Swoole](https://www.swoole.co.uk/).

## Installation

You may use Composer to install Shadowfax to your project:

```shell
composer require huang-yi/shadowfax
```

If you are using Lumen, you need to register the service provider manually in `bootstrap/app.php`:

```php
$app->register(HuangYi\Shadowfax\ShadowfaxServiceProvider::class);
```

After installing Shadowfax, publish its configuration files using the `shadowfax:publish` Artisan command:

```shell
php artisan shadowfax:publish
```

## Configuration

The primary configuration file is `shadowfax.yml`. And this file name is added to the `.gitignore` file by `shadowfax:publish` Artisan command.

1. Basic configuration:

- **name**: The processes name.
- **type**: The server type, support: `http`, `websocket`.
- **host**: The server host.
- **port**: The server port.
- **mode**: The server mode, support: `process`, `base`.
- **access_log**: Indicates whether to print the request information.
- **app_pool_capacity**: Set the capacity of the apps pool. Only valid when coroutine is enabled.
- **framework_bootstrapper**: Set Laravel bootstrap file. If you changed Laravel's directory structure, you should modify this value.

2. `server` configuration：

This section defines the `Swoole\Server` configuration. Read the [official docs](https://www.swoole.co.uk/docs/modules/swoole-server/configuration) for more information.

3. `abstracts` configuration:

This option allows you to set a group of abstracts in the Laravel IoC container. These abstracts will be rebound after each request.

4. `controllers` configuration:

This option allows you to clean the controller instances in route after each request.

```yaml
controllers:
  - App\Http\Controllers\FooController
  - App\Http\Controllers\BarController
```

5. `cleaners` configuration:

This option allows you to register custom cleaners. These cleaners will run after each request.

```yaml
cleaners:
  - app/Cleaners/
  - CustomNamespace/FooCleaner
```

6. `db_pools` configuration：

This option allows you to configure database connection pools. You can add multiple key-value pairs in here.
The key name is a connection name in your `database.connections`, the key value is the connection pool capacity. e.g.:

```yaml
db_pools:
  mysql: 3
  mysql2: 5
```

7. `redis_pools` configuration：

This option allows you to configure redis connection pools. You can add multiple key-value pairs in here.
The key name is a connection name in your `database.redis`, the key value is the connection pool capacity. e.g.:

```yaml
redis_pools:
  default: 3
```

8. `controller server` configuration：

This section defines the controller server configuration. The controller server allows you to stop or reload your Shadowfax.

## Command

Shadowfax provides a `shadowfax` command to manage your server processes. This command is build on the Symfony console component, so you can run `php shadowfax list` for more information.

You may run the `php shadowfax start` command to start Shadowfax server. The `--watch|-w` option can run your Shadowfax in watch mode. In watch mode, the processes will be automatically reloaded when the files under your project change.

> You must install the [fswatch](https://github.com/emcrisostomo/fswatch) before using `--watch|-w` option.

The `php shadowfax reload` allows you to reload the Shadowfax processes.

The `php shadowfax stop` allows you to stop the Shadowfax server.

## Database Connection Pool

Before using database connection pools, you must enable Swoole coroutine and configure the hook_flags:

```yaml
server:
  enable_coroutine: true
  hook_flags: SWOOLE_HOOK_ALL
```

Then, add your connection to the `db_pools` option, and specify a pool capacity:

```yaml
db_pools:
  mysql: 3
  mysql2: 5
```

## Redis Connection Pool

The difference with database connection pool is that redis connection pools are configured under the `redis_pools` option.

```yaml
redis_pools:
  default: 3
```

## WebSocket Server

Shadowfax also allows you to build your WebSocket server.

First of all, you need to change the value of configuration item `type` to `websocket`. Then create a handler class that implemented the `HuangYi\Shadowfax\Contracts\WebSocket\Handler` interface:

```php
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
        $connection->send('connected');
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
        $connection->send('closed');
    }
}

```

And bind this handler to a uri in your route file:

```php
use App\WebSocket\EchoServer;
use HuangYi\Shadowfax\Facades\WebSocket;

WebSocket::listen('/echo', new EchoServer);

```

Now, you can start the WebSocket server by command `php shadowfax start`.

## Nginx Configuration

You may use Nginx as a reverse proxy in production environment:

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

> You need to add the IP address of the Shadowfax to the App\Http\Middleware\TrustProxies Middleware.

## Supervisor Configuration

If you want to use the Supervisor to manage your Shadowfax processes, the following configuration file should suffice:

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

Run tests using [wrk](https://github.com/wg/wrk).

### Environment 1

- Hardware: 1 CPU, 4 Cores, 16GB Memory
- MacOS 10.15.3
- PHP 7.3.12 (with opcache)
- Swoole 4.4.13
- Laravel 7 (without session middleware)
- Shadowfax 2.0.0 (with 20 worker processes)

```shell
wrk -t4 -c200 http://127.0.0.1:1215/
```

Result:

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

### Environment 2

- Hardware: 2 CPUs, 2 Cores, 4GB Memory
- CentOS 7.5.1804
- PHP 7.3.16 (with opcache)
- Swoole 4.4.17
- Laravel 7 (without session middleware)
- Shadowfax 2.0.0 (with 10 worker processes)

```shell
wrk -c100 http://127.0.0.1:1215/
```

Result:

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

## Testing

```shell
composer test
```

## License

Shadowfax is open-sourced software licensed under the [MIT license](LICENSE).
