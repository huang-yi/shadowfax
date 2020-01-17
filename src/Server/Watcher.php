<?php

namespace HuangYi\Shadowfax\Server;

use HuangYi\Watcher\Commands\Fswatch;
use Swoole\Process;
use Symfony\Component\Console\Input\ArrayInput;

class Watcher extends Action
{
    /**
     * Indicates if the server is reloading.
     *
     * @var bool
     */
    protected $reloading = false;

    /**
     * The watched events.
     *
     * @var array
     */
    protected $events = [
        Fswatch::CREATED, Fswatch::UPDATED, Fswatch::REMOVED, Fswatch::RENAMED,
        Fswatch::MOVED_FROM, Fswatch::MOVED_TO,
    ];

    /**
     * Watch the server.
     *
     * @return void
     */
    public function watch()
    {
        $this->startServer();

        $this->startFswatch();
    }

    /**
     * Start the server.
     *
     * @return void
     */
    protected function startServer()
    {
        $process = new Process(function () {
            (new Starter($this->input, $this->output))->start();
        });

        $process->start();
    }

    /**
     * Start the fswatch.
     *
     * @return void
     */
    protected function startFswatch()
    {
        $process = new Process(function ($process) {
            $command = new Fswatch(SHADOWFAX_PATH);

            $command->setOptions([
                '--event'       => $this->getWatchedEvents(),
                '--one-event'   => true,
                '--recursive'   => true,
                '--filter-from' => $this->getFilterFile(),
            ]);

            $process->exec($command->getBinary(), $command->getArguments());
        }, true);

        $process->start();

        Process::wait(false);

        swoole_event_add($process->pipe, function () use ($process) {
            $process->read();

            if (! $this->reloading) {
                $this->reloading = true;

                $this->reloadServer();

                $this->reloading = false;
            }
        });
    }

    /**
     * Get watched events.
     *
     * @return int
     */
    protected function getWatchedEvents()
    {
        $events = 0;

        foreach ($this->events as $event) {
            $events |= $event;
        }

        return $events;
    }

    /**
     * Get the fswatch filter rules path.
     *
     * @return string
     */
    protected function getFilterFile()
    {
        if (file_exists('.watch')) {
            $path = '.watch';
        } else {
            $path = __DIR__.'/../../.watch';
        }

        return realpath($path);
    }

    /**
     * Reload the server.
     *
     * @return void
     */
    protected function reloadServer()
    {
        (new Reloader($this->input, $this->output))->reload();
    }
}
