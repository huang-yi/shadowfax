<?php

namespace HuangYi\Shadowfax;

class ContainerRewriter
{
    /**
     * The path of "container.php"
     *
     * @var string
     */
    protected $path = __DIR__.'/../container.php';

    /**
     * Rewrite the container.
     *
     * @return void
     */
    public function rewrite()
    {
        $search = <<<'SEARCH'
        return static::$instance;
SEARCH;

        $replace = <<<'REPLACE'
        shadowfax_correct_container(static::$instance);

        return static::$instance;
REPLACE;

        file_put_contents(
            $this->getPath(),
            str_replace($search, $replace, file_get_contents($this->getSourcePath()))
        );
    }

    /**
     * Set the path.
     *
     * @param  string  $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the container file path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get thee container source path.
     *
     * @return string
     */
    public function getSourcePath()
    {
        $path = 'vendor/laravel/framework/src/Illuminate/Container/Container.php';

        if (! file_exists($path)) {
            $path = 'vendor/illuminate/container/Container.php';
        }

        if (! file_exists($path)) {
            $path = __DIR__.'/../vendor/laravel/framework/src/Illuminate/Container/Container.php';
        }

        if (! file_exists($path)) {
            $path = __DIR__.'/../vendor/illuminate/container/Container.php';
        }

        return $path;
    }
}
