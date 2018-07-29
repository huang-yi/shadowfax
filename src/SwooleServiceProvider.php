<?php

namespace HuangYi\Swoole;

use HuangYi\Swoole\Console\ServerCommand;
use HuangYi\Swoole\WebSocket\JoinRoom;
use HuangYi\Swoole\WebSocket\JsonParser;
use HuangYi\Swoole\WebSocket\Router;
use HuangYi\Swoole\WebSocket\WebSocket;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class SwooleServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     * @throws \HuangYi\Swoole\Exceptions\TableCreationFailedException
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerTable();
        $this->registerWebSocket();
        $this->registerServer();
        $this->registerCommands();
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/swoole.php' => base_path('config/swoole.php')
        ], 'config');
    }

    /**
     * Merge configurations.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/swoole.php', 'swoole');
    }

    /**
     * Register table.
     *
     * @return void
     * @throws \HuangYi\Swoole\Exceptions\TableCreationFailedException
     */
    protected function registerTable()
    {
        $tables = $this->app['config']['swoole.tables'];

        $tableCollection = new TableCollection($tables);

        $this->app->instance('swoole.tables', $tableCollection);
    }

    /**
     * Register WebSocket.
     *
     * @return void
     */
    protected function registerWebSocket()
    {
        $this->app->singleton('swoole.websocket.router', function ($app) {
            return new Router($app['events'], $app);
        });

        $this->app->singleton('swoole.websocket.kernel', function ($app) {
            $class = get_class($app[Kernel::class]);

            $kernel = new $class($app, $app['swoole.websocket.router']);

            $kernel->pushMiddleware(JoinRoom::class);

            return $kernel;
        });

        $this->app->singleton('swoole.websocket.parser', function ($app) {
            $parserClass = $app['config']->get('swoole.message_parser', JsonParser::class);

            return $app->make($parserClass);
        });

        $this->app->singleton('swoole.websocket', function ($app) {
            $connection = $app['config']->get('swoole.redis_connection', 'default');
            $redis = $app['redis']->connection($connection);

            return (new WebSocket($app))->setRedis($redis);
        });
    }

    /**
     * Register server.
     *
     * @return void
     */
    protected function registerServer()
    {
        $this->app->singleton('swoole.server', function ($app) {
            return new ServerManager($app);
        });
    }

    /**
     * Register commands.
     */
    protected function registerCommands()
    {
        $this->commands([
            ServerCommand::class,
        ]);
    }
}
