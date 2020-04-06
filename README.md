English | [中文](README-cn.md)

# Shadowfax

The Shadowfax is a package that runs your Laravel application on [Swoole](https://www.swoole.co.uk/).

## Installation

You may use Composer to install Shadowfax to your project:

```shell
composer require huang-yi/shadowfax
```

After installing Shadowfax, publish its configuration files using the `vendor:publish` Artisan command:

```shell
php artisan vendor:publish --provider="HuangYi\Shadowfax\ShadowfaxServiceProvider"
```

## Configuration

You should copy the `shadowfax.yml.example` file to a new file named `shadowfax.yml` in the root directory of your application. And you'd better add the `shadowfax.yml` file to the `.gitignore`.

1. Basic configuration:

- **name**: The processes name.
- **host**: The server host.
- **port**: The server port.
- **mode**: The server mode, support: `base`, `process`.
- **access_log**: Indicates whether to print the request information.
- **runtime_hooks**: Set the Swoole runtime hooks.
- **app_pool_capacity**: Set the capacity of the apps pool. Only valid if coroutine is enabled.
- **bootstrap**: Set Laravel bootstrap file. If you changed Laravel's directory structure, you should modify this value.

2. `server` configuration：

This section defines the `swoole-server` configuration. Read the [official docs](https://www.swoole.co.uk/docs/modules/swoole-server/configuration) for more information.

3. `controller` configuration：

This section defines the controller server configuration. The controller server allows you to stop or reload your Shadowfax.

## Command

Shadowfax provides the `./vendor/bin/shadowfax` command to manage your server processes. This command is build on the Symfony console component, so you can run `./vendor/bin/shadowfax list` for more information.

You may run the `./vendor/bin/shadowfax start` command to start Shadowfax server. The `--watch` option can run your Shadowfax in watch mode. In watch mode, the processes will be automatically reloaded when the files under your project change.

> You must install the [fswatch](https://github.com/emcrisostomo/fswatch) before using `--watch` option.

The `./vendor/bin/shadowfax reload` allows you to reload the Shadowfax processes.

The `./vendor/bin/shadowfax stop` allows you to stop the Shadowfax server.

## Nginx configuration

You may use Nginx as a reverse proxy in production environment:

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

> You need to add the IP address of the Shadowfax to the App\Http\Middleware\TrustProxies Middleware.

## Supervisor configuration

If you want to use the Supervisor to manage your Shadowfax processes, the following configuration file should suffice:

```ini
[program:shadowfax]
process_name=%(program_name)s
directory=/path/to/project
command=php vendor/bin/shadowfax start
autostart=true
autorestart=true
user=www
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/supervisor.log
```

## Testing

```shell
composer test
```

## License

Shadowfax is open-sourced software licensed under the [MIT license](LICENSE).
