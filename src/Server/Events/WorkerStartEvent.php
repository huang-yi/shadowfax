<?php

namespace HuangYi\Shadowfax\Events;

use HuangYi\Shadowfax\ApplicationFactory;
use HuangYi\Shadowfax\Composer;
use HuangYi\Shadowfax\Config;
use HuangYi\Shadowfax\ContainerRewriter;

class WorkerStartEvent extends Event
{
    /**
     * Handle the event.
     *
     * @param  mixed  ...$args
     * @return void
     */
    public function handle(...$args)
    {
        $this->outputProcessInfo(...$args);

        $this->clearCaches();

        $this->registerAutoload();

        $this->createApplicationFactory(...$args);
    }

    /**
     * Output the process information.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return void
     */
    protected function outputProcessInfo($server, $workerId)
    {
        $type = $this->isTaskProcess($server, $workerId) ? 'task worker' : 'worker';

        $this->output->writeln("<info>[√] $type process started. [{$server->worker_pid}]</info>");

        $host = $this->isSingleProcess($server) ? " {$server->host}:{$server->port}" : '';

        shadowfax_set_process_name(sprintf(
            '%s: %s process%s',
            $this->getName(),
            $type,
            $host
        ));
    }

    /**
     * Determine if the process is a task process.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return bool
     */
    public function isTaskProcess($server, $workerId)
    {
        return $server->setting['worker_num'] <= $workerId;
    }

    /**
     * Determine if the Shadowfax is single process.
     *
     * @param  \Swoole\Server  $server
     * @return bool
     */
    public function isSingleProcess($server)
    {
        if ($server->mode != SWOOLE_BASE) {
            return false;
        }

        return $server->setting['worker_num'] == 1;
    }

    /**
     * Clear caches.
     *
     * @return void
     */
    protected function clearCaches()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
    }

    /**
     * Load files.
     *
     * @return void
     */
    protected function registerAutoload()
    {
        $this->shadowfax()->make(Composer::class)->reload();

        require $this->shadowfax()->make(ContainerRewriter::class)->getPath();
    }

    /**
     * Create the application factory.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return void
     */
    protected function createApplicationFactory($server, $workerId)
    {
        $factory = new ApplicationFactory(
            $this->getBootstrapPath(),
            $this->getKernelType($server, $workerId),
            $this->getPoolCapacity()
        );

        $this->shadowfax()->instance(ApplicationFactory::class, $factory);
    }

    /**
     * Get the framework bootstrap path.
     *
     * @return string
     */
    protected function getBootstrapPath()
    {
        return $this->shadowfax()->make(Config::class)->get('bootstrap');
    }

    /**
     * Get the Laravel applications count
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return int
     */
    protected function getKernelType($server, $workerId)
    {
        if ($this->isTaskProcess($server, $workerId)) {
            return ApplicationFactory::TYPE_CONSOLE;
        }

        return ApplicationFactory::TYPE_HTTP;
    }

    /**
     * Get the pool capacity.
     *
     * @return int
     */
    protected function getPoolCapacity()
    {
        return (int) $this->shadowfax()->make(Config::class)->get('applications', 10);
    }
}
