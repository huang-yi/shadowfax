<?php

namespace HuangYi\Shadowfax\Console;

use HuangYi\Shadowfax\Server\Reloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReloadCommand extends Command
{
    /**
     * Set the command name.
     *
     * @var string
     */
    protected static $defaultName = 'reload';

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        (new Reloader($input, $output))->reload();
    }

    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Reload the Shadowfax server.')
            ->setHelp('This command allows you to reload the Shadowfax server.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Shadowfax configuration file.')
            ->addOption('task', 't', InputOption::VALUE_OPTIONAL, 'Reload all task worker process.', false)
        ;
    }
}
