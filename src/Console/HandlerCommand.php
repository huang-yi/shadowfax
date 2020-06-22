<?php

namespace HuangYi\Shadowfax\Console;

use Illuminate\Console\GeneratorCommand;

class HandlerCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shadowfax:handler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new handler class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Handler';

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
        return __DIR__.'/../../stubs/handler.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\WebSocket\Handlers';
    }
}
