<?php

namespace HuangYi\Shadowfax\Console;

use HuangYi\Shadowfax\Server\Watcher;
use Swoole\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WatchCommand extends Command
{
    /**
     * Set the command name.
     *
     * @var string
     */
    protected static $defaultName = 'watch';

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        (new Watcher($input, $output))->watch();
    }

    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Start the Shadowfax server (watch mode).')
            ->setHelp('This command allows you to start the Shadowfax server in watch mode.')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Shadowfax server host.')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Shadowfax server port.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Shadowfax configuration file.')
        ;
    }
}
