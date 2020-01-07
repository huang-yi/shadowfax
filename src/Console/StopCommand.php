<?php

namespace HuangYi\Shadowfax\Console;

use HuangYi\Shadowfax\Server\Stopper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StopCommand extends Command
{
    /**
     * Set the command name.
     *
     * @var string
     */
    protected static $defaultName = 'stop';

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        (new Stopper($input, $output))->stop();
    }

    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Stop the Shadowfax server.')
            ->setHelp('This command allows you to stop the Shadowfax server.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Shadowfax configuration file.')
        ;
    }
}
