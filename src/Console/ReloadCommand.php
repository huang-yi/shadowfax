<?php

namespace HuangYi\Shadowfax\Console;

use Swoole\Coroutine;
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

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input);

        Coroutine::create(function () use ($input, $output) {
            $client = $this->httpClient();

            if ($input->getOption('task') === false) {
                $output->writeln("<info>Reload all the Shadowfax worker processes.</info>");

                $client->get('/reload');
            } else {
                $output->writeln("<info>Reload all the Shadowfax task worker processes.</info>");

                $client->get('/reload-task');
            }

            if ($client->errCode !== 0) {
                $output->writeln("<error>Cannot connect the Shadowfax controller server. [{$client->errCode}]</error>");

                return;
            }

            if ($client->statusCode !== 200) {
                $output->writeln("<error>Failed to reload the Shadowfax server: {$client->body}</error>");

                return;
            }

            $output->writeln("<info>The Shadowfax server reloaded.</info>");
        });

        return 0;
    }
}
