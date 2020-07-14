<?php

namespace HuangYi\Shadowfax\Console;

use HuangYi\Shadowfax\Bootstrap\LoadConfiguration;
use HuangYi\Shadowfax\Shadowfax;
use Swoole\Coroutine\Http\Client;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;

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
        list($host, $port) = $this->getControllerServerHostAndPort();

        return new Client($host, $port);
    }

    /**
     * Get the controller server host and port.
     *
     * @return array
     */
    protected function getControllerServerHostAndPort()
    {
        if (! $option = $this->config('controller_server')) {
            $option = $this->config('controller');
        }

        return [
            $option['host'] ?? '127.0.0.1',
            $option['port'] ?? 1216,
        ];
    }

    /**
     * Bootstrap the Shadowfax.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return void
     */
    protected function bootstrap(InputInterface $input)
    {
        $this->setUserConfigurationFile($input);

        $this->shadowfax->bootstrap();
    }

    /**
     * Set the user configuration file path.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return void
     */
    protected function setUserConfigurationFile(InputInterface $input)
    {
        if ($config = $input->getOption('config')) {
            LoadConfiguration::setUserFile($config);
        }
    }
}
