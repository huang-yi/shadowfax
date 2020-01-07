<?php

namespace HuangYi\Shadowfax\Server;

use HuangYi\Shadowfax\Config;
use HuangYi\Shadowfax\Shadowfax;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Controller
{
    /**
     * The console input.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * The console output.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * The configuration instance.
     *
     * @var \HuangYi\Shadowfax\Config
     */
    protected $config;

    /**
     * The server controller.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->loadConfig();
    }

    /**
     * Load configurations.
     *
     * @return void
     */
    protected function loadConfig()
    {
        $userPath = $this->input->getOption('config');

        if ($userPath) {
            if (! file_exists($userPath)) {
                $this->output->writeln("<error>Cannot find configuration file [$userPath].</error>");

                exit(1);
            }

            $userPath = $this->shadowfax()->basePath($userPath);
        }

        $this->shadowfax()->instance(Config::class, $this->config = new Config($userPath));
    }

    /**
     * Get the Shadowfax instance.
     *
     * @return \HuangYi\Shadowfax\Shadowfax
     */
    public function shadowfax()
    {
        return Shadowfax::getInstance();
    }

    /**
     * Get the configuration option.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return $this->shadowfax()->make(Config::class)->get($key, $default);
    }
}
