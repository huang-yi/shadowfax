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
        $this->start();

        $this->fswatch();
    }

    /**
     * Start the server.
     *
     * @return void
     */
    protected function start()
    {
        $process = new Process(function () {
            $command = $this->shadowfax()->find('start');

            $arguments = [
                'command' => 'start',
                '--host' => $this->input->getOption('host'),
                '--port' => $this->input->getOption('port'),
                '--config' => $this->input->getOption('config'),
            ];

            $input = new ArrayInput($arguments);
            $command->run($input, $this->output);
        });

        $process->start();
    }

    /**
     * Start the fswatch.
     *
     * @return void
     */
    protected function fswatch()
    {
        $process = new Process(function ($process) {
            $command = new Fswatch($this->shadowfax()->basePath('../../..'));

            $command->setOptions([
                '--event'       => $this->getWatchedEvents(),
                '--recursive'   => true,
                '--filter-from' => $this->getFilterFile(),
            ]);

            $process->exec($command->getBinary(), $command->getArguments());
        }, true);

        $process->start();

        swoole_event_add($process->pipe, function () use ($process) {
            $process->read();

            if (! $this->reloading) {
                $this->reloading = true;

                $this->reload();

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
        $path = $this->shadowfax()->basePath('../../../.watch');

        if (! file_exists($path)) {
            $path = __DIR__.'/../../.watch';
        }

        return realpath($path);
    }

    /**
     * Reload the server.
     *
     * @return void
     */
    protected function reload()
    {
        $command = $this->shadowfax()->find('reload');

        $arguments = [
            'command' => 'reload',
            '--config' => $this->input->getOption('config'),
        ];

        $input = new ArrayInput($arguments);
        $command->run($input, $this->output);
    }
}
