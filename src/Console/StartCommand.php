<?php

namespace HuangYi\Shadowfax\Console;

use HuangYi\Shadowfax\Contracts\ServerFactory;
use HuangYi\Shadowfax\Events\ControllerRequestEvent;
use HuangYi\Shadowfax\Events\StartingEvent;
use HuangYi\Shadowfax\Factories\HttpServerFactory;
use HuangYi\Shadowfax\Factories\WebSocketServerFactory;
use HuangYi\Watcher\Commands\Fswatch;
use Swoole\Process;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    /**
     * The default command name.
     *
     * @var string
     */
    protected static $defaultName = 'start';

    /**
     * Indicates if the server is reloading.
     *
     * @var bool
     */
    protected $reloading = false;

    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Start the Shadowfax server.')
            ->setHelp('This command allows you to start the Shadowfax server.')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Shadowfax server host.')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Shadowfax server port.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Shadowfax configuration file.')
            ->addOption('watch', 'w', InputOption::VALUE_OPTIONAL, 'Run server in watch mode.', false);
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input);

        $this->shadowfax->instance('output', $output);

        if ($input->getOption('watch') === false) {
            $this->start($input, $output);
        } else {
            $this->watch($input, $output);
        }
    }

    /**
     * Start the Shadowfax.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function start(InputInterface $input, OutputInterface $output)
    {
        $server = $this->getFactory()
            ->setHost($this->getHost($input))
            ->setPort($this->getPort($input))
            ->setMode($this->getMode())
            ->setSettings($this->getSettings())
            ->create();

        $this->listenControllerPort($server);

        $this->shadowfax->instance('server', $server);

        $this->shadowfax['events']->dispatch(new StartingEvent);

        $output->writeln("<info>Starting the Shadowfax server:</info> <comment>{$server->host}:{$server->port}</comment>");

        $server->start();
    }

    /**
     * Run server in watch mode.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function watch(InputInterface $input, OutputInterface $output)
    {
        $this->startServerInProcess($input);

        $this->startFswatchProcess($output);
    }

    /**
     * Get the server factory
     *
     * @return \HuangYi\Shadowfax\Contracts\ServerFactory
     */
    protected function getFactory(): ServerFactory
    {
        if ($this->config('type', 'websocket')) {
            return new WebSocketServerFactory($this->shadowfax['events']);
        }

        return new HttpServerFactory($this->shadowfax['events']);
    }

    /**
     * Get the server host.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return string
     */
    protected function getHost(InputInterface $input)
    {
        if ($host = $input->getOption('host')) {
            return $host;
        }

        return $this->config('host', '127.0.0.1');
    }

    /**
     * Get the server port.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return string
     */
    protected function getPort(InputInterface $input)
    {
        if ($port = $input->getOption('port')) {
            return (int) $port;
        }

        return (int) $this->config('port', 1215);
    }

    /**
     * Get the server mode.
     *
     * @return string
     */
    protected function getMode()
    {
        return $this->config('mode') == 'base' ? SWOOLE_BASE : SWOOLE_PROCESS;
    }

    /**
     * Get the server settings.
     *
     * @return array
     */
    protected function getSettings()
    {
        return (array) $this->config('server', []);
    }

    /**
     * Listen the controller port.
     *
     * @param  \Swoole\Server  $server
     * @return void
     */
    protected function listenControllerPort($server)
    {
        $port = $server->addListener(
            $this->config('controller.host', '127.0.0.1'),
            $this->config('controller.port', 1216),
            SWOOLE_SOCK_TCP
        );

        $port->on('request', function (...$args) {
            $this->shadowfax['events']->dispatch(new ControllerRequestEvent(...$args));
        });
    }

    /**
     * Start the Shadowfax in a process.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return void
     */
    protected function startServerInProcess(InputInterface $input)
    {
        $process = new Process(function () use ($input) {
            $this->start($input);
        });

        $process->start();
    }

    /**
     * Start the fswatch process.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function startFswatchProcess(OutputInterface $output)
    {
        $command = new Fswatch($this->shadowfax->basePath());

        $command->setOptions([
            '--event'       => $this->getWatchedEvents(),
            '--recursive'   => true,
            '--filter-from' => $this->getFilterFile(),
        ]);

        $process = new Process(function ($process) use ($command) {
            $process->exec($command->getBinary(), $command->getArguments());
        }, true);

        $process->start();

        Process::wait(false);

        swoole_event_add($process->pipe, function () use ($process, $command, $output) {
            $command->parseEvents($process->read());

            if (! $this->reloading) {
                $this->reloading = true;

                $this->reload($output);

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
        $events = [
            Fswatch::CREATED, Fswatch::UPDATED, Fswatch::REMOVED,
            Fswatch::RENAMED, Fswatch::MOVED_FROM, Fswatch::MOVED_TO,
        ];

        $value = 0;

        foreach ($events as $event) {
            $value |= $event;
        }

        return $value;
    }

    /**
     * Get the fswatch filter rules path.
     *
     * @return string
     */
    protected function getFilterFile()
    {
        $path = $this->shadowfax->basePath('.watch');

        if (! file_exists($path)) {
            $path = __DIR__.'/../../.watch';
        }

        return realpath($path);
    }

    /**
     * Reload the Shadowfax.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function reload(OutputInterface $output)
    {
        $command = $this->shadowfax->getConsole()->find('reload');

        $command->run(new ArrayInput(['command' => 'reload']), $output);
    }
}
