<?php

namespace HuangYi\Shadowfax\Server\Events;

use HuangYi\Shadowfax\ApplicationFactory;
use HuangYi\Shadowfax\Config;
use HuangYi\Shadowfax\Shadowfax;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Event
{
    /**
     * The console output.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Event constructor.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Handle the event.
     *
     * @param  mixed  ...$args
     * @return void
     */
    abstract public function handle(...$args);

    /**
     * Set the process name.
     *
     * @param  string  $name
     * @return void
     */
    protected function setProcessName($name)
    {
        if (PHP_OS != 'Darwin') {
            swoole_set_process_name($name);
        }
    }

    /**
     * Determine if the Shadowfax is single process.
     *
     * @param  \Swoole\Server  $server
     * @return bool
     */
    protected function isSingleProcess($server)
    {
        if ($server->mode != SWOOLE_BASE) {
            return false;
        }

        return $server->setting['worker_num'] == 1;
    }

    /**
     * Get the Shadowfax container.
     *
     * @return \HuangYi\Shadowfax\Shadowfax
     */
    public function shadowfax()
    {
        return Shadowfax::getInstance();
    }

    /**
     * Get the application factory.
     *
     * @return \HuangYi\Shadowfax\ApplicationFactory
     */
    protected function appFactory()
    {
        return $this->shadowfax()->make(ApplicationFactory::class);
    }

    /**
     * Get config item.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    protected function getConfig($key, $default = null)
    {
        return $this->shadowfax()->make(Config::class)->get($key, $default);
    }

    /**
     * Get the name.
     *
     * @return string
     */
    protected function getName()
    {
        return $this->getConfig('name', 'shadowfax');
    }
}
