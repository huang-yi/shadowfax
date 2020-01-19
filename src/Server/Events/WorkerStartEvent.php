<?php

namespace HuangYi\Shadowfax\Server\Events;

use HuangYi\Shadowfax\Composer;
use HuangYi\Shadowfax\ContainerRewriter;
use HuangYi\Shadowfax\Contracts\AppFactory as AppFactoryContract;
use HuangYi\Shadowfax\Exceptions\InvalidBootstrapException;
use HuangYi\Shadowfax\Factories\AppFactory;
use HuangYi\Shadowfax\Factories\CoroutineAppFactory;
use HuangYi\Shadowfax\FrameworkBootstrapper;
use Swoole\Runtime;

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
        chdir(SHADOWFAX_PATH);

        $this->clearCaches();

        $this->registerAutoload();

        $this->createAppFactory(...$args);

        $this->enableRuntimeCoroutine();

        $this->outputProcessInfo(...$args);
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
    protected function createAppFactory($server, $workerId)
    {
        $bootstrapper = $this->createFrameworkBootstrapper($server, $workerId);

        if ($this->isCoroutineEnabled($server, $workerId)) {
            $factory = new CoroutineAppFactory(
                $bootstrapper,
                $this->config('app_pool_capacity', 10)
            );
        } else {
            $factory = new AppFactory($bootstrapper);
        }

        $this->shadowfax()->instance(AppFactoryContract::class, $factory);
    }

    /**
     * Create the framework bootstrapper.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return \HuangYi\Shadowfax\FrameworkBootstrapper
     */
    protected function createFrameworkBootstrapper($server, $workerId)
    {
        return new FrameworkBootstrapper(
            $this->getKernelType($server, $workerId),
            $this->getUserBootstrapPath()
        );
    }

    /**
     * Enable runtime coroutine.
     *
     * @return void
     */
    protected function enableRuntimeCoroutine()
    {
        if ($hooks = (int) $this->config('runtime_hooks')){
            Runtime::enableCoroutine($hooks);
        }
    }

    /**
     * Determine if the coroutine is enabled.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return int
     */
    protected function isCoroutineEnabled($server, $workerId)
    {
        if ($this->isTaskProcess($server, $workerId)) {
            return $server->setting['task_enable_coroutine'];
        }

        return $server->setting['enable_coroutine'];
    }

    /**
     * Get the kernel type.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return int
     */
    protected function getKernelType($server, $workerId)
    {
        if ($this->isTaskProcess($server, $workerId)) {
            return FrameworkBootstrapper::TYPE_CONSOLE;
        }

        return FrameworkBootstrapper::TYPE_HTTP;
    }

    /**
     * Get user bootstrap path.
     *
     * @return string
     */
    protected function getUserBootstrapPath()
    {
        if ($userPath = $this->config('bootstrap')) {
            if (file_exists($userPath)) {
                $userPath = realpath($userPath);
            } else {
                throw new InvalidBootstrapException(
                    "The framework bootstrap file [{$userPath}] does not exists."
                );
            }
        }

        return $userPath;
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

        $this->setProcessName(sprintf(
            '%s: %s process%s',
            $this->getName(),
            $type,
            $host
        ));
    }
}
