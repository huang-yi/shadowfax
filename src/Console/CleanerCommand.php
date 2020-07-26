<?php

namespace HuangYi\Shadowfax\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class CleanerCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shadowfax:cleaner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new cleaner class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Cleaner';

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return class_exists($rawName);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../../stubs/cleaner.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Cleaners';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        return $this->replaceInterface(parent::buildClass($name));
    }

    /**
     * Replace the interface name for the given stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function replaceInterface($stub)
    {
        $interface = $this->option('before') ? 'BeforeCleaner' : 'Cleaner';

        return str_replace('DummyInterface', $interface, $stub);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['before', 'b', InputOption::VALUE_NONE, 'Indicates that cleaner should run before request'],
        ];
    }
}
