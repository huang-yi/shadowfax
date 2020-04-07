<?php

namespace HuangYi\Shadowfax\Bootstrap;

use HuangYi\Shadowfax\Shadowfax;
use Symfony\Component\Yaml\Yaml;

class LoadConfiguration
{
    /**
     * User defined configuration file path.
     *
     * @var string
     */
    protected static $userFile;

    /**
     * Load the configuration.
     *
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @return void
     */
    public function bootstrap(Shadowfax $shadowfax)
    {
        $file = $this->getConfigFile($shadowfax);

        $items = Yaml::parseFile($file);

        foreach ($items as $key => $value) {
            $shadowfax['config']->set($key, $value);
        }
    }

    /**
     * Get the configuration file path.
     *
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @return string
     */
    protected function getConfigFile(Shadowfax $shadowfax)
    {
        $file = static::$userFile ?: 'shadowfax.yml';

        if ($file[0] != '/') {
            $file = $shadowfax->basePath('shadowfax.yml');
        }

        if (! file_exists($file)) {
            $file = __DIR__.'/../../shadowfax.yml';
        }

        return $file;
    }

    /**
     * Set the user defined configuration file path.
     *
     * @param  string  $path
     * @return void
     */
    public static function setUserFile(string $path)
    {
        static::$userFile = $path;
    }
}
