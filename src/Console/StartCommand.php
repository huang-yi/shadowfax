<?php

namespace HuangYi\Shadowfax\Console;

use HuangYi\Shadowfax\Server\Starter;
use HuangYi\Shadowfax\Server\Watcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    /**
     * Set the command name.
     *
     * @var string
     */
    protected static $defaultName = 'start';

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('watch') === false) {
            (new Starter($input, $output))->start();
        } else {
            (new Watcher($input, $output))->watch();
        }
    }

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
            ->addOption('watch', 'w', InputOption::VALUE_OPTIONAL, 'Run server in watch mode.', false)
        ;
    }
}
