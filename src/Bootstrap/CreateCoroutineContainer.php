<?php

namespace HuangYi\Shadowfax\Bootstrap;

use HuangYi\Shadowfax\Shadowfax;

class CreateCoroutineContainer
{
    /**
     * The path to output coroutine container class.
     *
     * @var string
     */
    protected $output = __DIR__.'/../coroutine_container.php';

    /**
     * Create the coroutine container.
     *
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @return void
     */
    public function bootstrap(Shadowfax $shadowfax)
    {
        $this->createCoroutineContainer($shadowfax);

        require $this->output;
    }

    /**
     * Create the coroutine container class file.
     *
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @return void
     */
    public function createCoroutineContainer(Shadowfax $shadowfax)
    {
        $search = <<<'SEARCH'
        return static::$instance;
SEARCH;

        $replace = <<<'REPLACE'
        shadowfax_correct_container(static::$instance);

        return static::$instance;
REPLACE;

        $source = $this->getIlluminateContainerFile($shadowfax);

        file_put_contents($this->output, str_replace(
            $search,
            $replace,
            file_get_contents($source)
        ));
    }

    /**
     * Get the Illuminate Container class file path.
     *
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @return string
     */
    protected function getIlluminateContainerFile(Shadowfax $shadowfax)
    {
        $file = $shadowfax->basePath('vendor/laravel/framework/src/Illuminate/Container/Container.php');

        if (! file_exists($file)) {
            $file = $shadowfax->basePath('vendor/illuminate/container/Container.php');
        }

        return $file;
    }

    /**
     * Get the output file path.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
}
