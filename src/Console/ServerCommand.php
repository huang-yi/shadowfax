<?php

namespace HuangYi\Swoole\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Swoole\Process;

class ServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:server {action=start : start|stop|restart|reload|watch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Swoole http/sebsocket server controller.';

    /**
     *
     * The pid.
     *
     * @var int
     */
    protected $pid;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->detectSwoole();

        $action = $this->getAction();

        $this->$action();
    }

    /**
     * Get command action.
     *
     * @return string
     */
    protected function getAction()
    {
        $action = $this->argument('action');

        if (! in_array($action, ['start', 'stop', 'restart', 'reload', 'watch'])) {
            $this->error("Invalid argument '$action'. ");

            exit(1);
        }

        return $action;
    }

    /**
     * Start server.
     *
     * @return void
     */
    protected function start()
    {
        if ($this->isRunning($this->getPid())) {
            $this->error('Failed! The swoole process is already running.');

            exit(1);
        }

        $this->info('Starting server...');
        $this->info('> (You can run this command to ensure the ' .
            'swoole process is running: ps -ef|grep "swoole")');

        $this->laravel->make('swoole.server')->start();
    }

    /**
     * Stop server.
     *
     * @return void
     */
    protected function stop()
    {
        $pid = $this->getPid();

        if (! $this->isRunning($pid)) {
            $this->error("Failed! There is no server process running.");

            exit(1);
        }

        $this->info('Stopping server...');

        $isRunning = $this->killProcess($pid, SIGTERM, 15);

        if ($isRunning) {
            $this->error('Unable to stop the server process.');

            exit(1);
        }

        // I don't known why Swoole didn't trigger "onShutdown" after sending SIGTERM.
        // So we should manually remove the pid file.
        $this->removePidFile();

        $this->info('> success');
    }

    /**
     * Restart server.
     *
     * @return void
     */
    protected function restart()
    {
        $pid = $this->getPid();

        if ($this->isRunning($pid)) {
            $this->stop();
        }

        $this->start();
    }

    /**
     * Reload server.
     *
     * @return void
     */
    protected function reload()
    {
        $pid = $this->getPid();

        if (! $this->isRunning($pid)) {
            $this->error("Failed! There is no server process running.");

            exit(1);
        }

        $this->info('Reloading server...');

        $isRunning = $this->killProcess($pid, SIGUSR1);

        if (! $isRunning) {
            $this->error('> failure');

            exit(1);
        }

        $this->info('> success');
    }

    /**
     * Watch server.
     *
     * @return void
     */
    public function watch()
    {
        if ($this->isRunning($this->getPid())) {
            $this->stop();
        }

        if ($this->isWatched()) {
            $this->removeWatchedFile();
        }

        $this->laravel['config']->set('swoole.server.options.daemonize', 0);

        $this->laravel['events']->listen('swoole.workerStart', function () {
            if ($this->createWatchedFile()) {
                $watcher = $this->createWatcher();
                $watcher->watch();
            }
        });

        $this->laravel['events']->listen('swoole.workerStop', function () {
            $this->removeWatchedFile();
        });

        $this->start();
    }

    /**
     * If swoole process is running.
     *
     * @param int $pid
     * @return bool
     */
    protected function isRunning($pid)
    {
        if (! $pid) {
            return false;
        }

        Process::kill($pid, 0);

        return ! swoole_errno();
    }

    /**
     * Kill process.
     *
     * @param int $pid
     * @param int $sig
     * @param int $wait
     * @return bool
     */
    protected function killProcess($pid, $sig, $wait = 0)
    {
        Process::kill($pid, $sig);

        if ($wait) {
            $start = time();

            do {
                if (! $this->isRunning($pid)) {
                    break;
                }

                usleep(100000);
            } while (time() < $start + $wait);
        }

        return $this->isRunning($pid);
    }

    /**
     * Get pid.
     *
     * @return int|null
     */
    protected function getPid()
    {
        if ($this->pid) {
            return $this->pid;
        }

        $pid = null;
        $path = $this->getPidPath();

        if (file_exists($path)) {
            $pid = (int) file_get_contents($path);

            if (! $pid) {
                $this->removePidFile();
            } else {
                $this->pid = $pid;
            }
        }

        return $this->pid;
    }

    /**
     * Get Pid file path.
     *
     * @return string
     */
    protected function getPidPath()
    {
        return $this->laravel['config']->get('swoole.server.options.pid_file');
    }

    /**
     * Remove Pid file.
     *
     * @return void
     */
    protected function removePidFile()
    {
        if (file_exists($this->getPidPath())) {
            unlink($this->getPidPath());
        }
    }

    /**
     * Detect if ext-swoole exists.
     *
     * @return void
     */
    protected function detectSwoole()
    {
        if (! extension_loaded('swoole')) {
            $this->error('The ext-swoole is required! (pecl install swoole)');

            exit(1);
        }
    }

    /**
     * Create watcher.
     *
     * @return \HuangYi\Watcher\Watcher
     */
    protected function createWatcher()
    {
        $config = $this->laravel['config']['swoole.watcher'];
        $directories = $config['directories'];
        $excludedDirectories = $config['excluded_directories'];
        $suffixes = $config['suffixes'];

        $watcher = new Watcher($directories, $excludedDirectories, $suffixes);

        return $watcher->setHandler(function () {
            $this->info('Reload server.');

            $this->laravel['swoole.server']->reload();
        });
    }

    /**
     * If watcher is running.
     *
     * @return bool
     */
    protected function isWatched()
    {
        return file_exists($this->getWatchedFile());
    }

    /**
     * Create watched flag file.
     *
     * @return bool
     */
    protected function createWatchedFile()
    {
        if (! $this->isWatched()) {
            return touch($this->getWatchedFile());
        }

        return false;
    }

    /**
     * Remove watched flag file.
     *
     * @return bool
     */
    protected function removeWatchedFile()
    {
        if ($this->isWatched()) {
            return unlink($this->getWatchedFile());
        }

        return false;
    }

    /**
     * Get watched flag file.
     *
     * @return string
     */
    protected function getWatchedFile()
    {
        return base_path('storage/logs/.watched');
    }
}
