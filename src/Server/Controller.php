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
     * The container.
     *
     * @var \HuangYi\Shadowfax\Shadowfax
     */
    protected $shadowfax;

    /**
     * The server controller.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @return void
     */
    public function __construct(InputInterface $input, OutputInterface $output, Shadowfax $shadowfax = null)
    {
        $this->input = $input;
        $this->output = $output;
        $this->shadowfax = $shadowfax ?: new Shadowfax;
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
        return $this->shadowfax->make(Config::class)->get($key, $default);
    }
}
