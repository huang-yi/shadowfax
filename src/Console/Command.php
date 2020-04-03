<?php

namespace HuangYi\Shadowfax\Console;

use HuangYi\Shadowfax\Shadowfax;
use Swoole\Coroutine\Http\Client;
use Symfony\Component\Console\Command\Command as BaseCommand;

class Command extends BaseCommand
{
    /**
     * The Shadowfax instance.
     *
     * @var \HuangYi\Shadowfax\Shadowfax
     */
    protected $shadowfax;

    /**
     * Create a new Command instance.
     *
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @param  string  $name
     * @return void
     */
    public function __construct(Shadowfax $shadowfax, string $name = null)
    {
        $this->shadowfax = $shadowfax;

        parent::__construct($name);
    }

    /**
     * Get config value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return $this->shadowfax['config']->get($key, $default);
    }

    /**
     * Get the HTTP client
     *
     * @return \Swoole\Coroutine\Http\Client
     */
    public function httpClient()
    {
        return new Client(
            $this->shadowfax['config']['controller.host'] ?: '127.0.0.1',
            $this->shadowfax['config']['controller.port'] ?: 1216
        );
    }
}
