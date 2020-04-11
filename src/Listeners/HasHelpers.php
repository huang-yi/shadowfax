<?php

namespace HuangYi\Shadowfax\Listeners;

use Closure;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Swoole\Coroutine;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

trait HasHelpers
{
    /**
     * Get the configuration option.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        return shadowfax('config')->get($key, $default);
    }

    /**
     * Get the name.
     *
     * @return string
     */
    protected function getName()
    {
        return shadowfax('config')['name'] ?: 'shadowfax';
    }

    /**
     * Get the worker process name.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return string
     */
    protected function getWorkerName($server, $workerId)
    {
        if ($this->isTaskWorker($server, $workerId)) {
            return 'task worker';
        }

        return 'worker';
    }

    /**
     * Determine if the process is a task worker process.
     *
     * @param  \Swoole\Server  $server
     * @param  int  $workerId
     * @return bool
     */
    protected function isTaskWorker($server, $workerId)
    {
        return $server->setting['worker_num'] <= $workerId;
    }

    /**
     * Determine if the Shadowfax is single process.
     *
     * @param  \Swoole\Server  $server
     * @return bool
     */
    protected function isSingleProcess($server)
    {
        if ($server->mode != SWOOLE_BASE) {
            return false;
        }

        return $server->setting['worker_num'] + $server->setting['task_worker_num'] == 1;
    }

    /**
     * Determine whether the current environment in coroutine.
     *
     * @return bool
     */
    protected function inCoroutine()
    {
        return Coroutine::getCid() > 0;
    }

    /**
     * Output the content to console.
     *
     * @param  string  $content
     * @return void
     */
    protected function output($content)
    {
        shadowfax('output')->writeln($content);
    }

    /**
     * Get the app pool.
     *
     * @return \HuangYi\Shadowfax\Contracts\AppPool
     */
    protected function appPool()
    {
        return shadowfax('app_pool');
    }

    /**
     * Handle logic without throwing any exception.
     *
     * @param  \Closure  $callback
     * @return mixed
     */
    protected function handleWithoutException(Closure $callback)
    {
        $app = $this->appPool()->pop();

        try {
            $callback($app);
        } catch (Exception $e) {
            $app[ExceptionHandler::class]->report($e);
        } catch (Throwable $e) {
            if (class_exists(FatalThrowableError::class)) {
                $e = new FatalThrowableError($e);
            }

            $app[ExceptionHandler::class]->report($e);
        }

        $this->appPool()->push($app);
    }
}
