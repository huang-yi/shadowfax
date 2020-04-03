<?php

namespace HuangYi\Shadowfax\Bootstrap;

use HuangYi\Shadowfax\Shadowfax;
use Symfony\Component\Yaml\Yaml;

class LoadConfiguration
{
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
        $file = $shadowfax->basePath('shadowfax.yml');

        if (! file_exists($file)) {
            $file = __DIR__.'/../../shadowfax.yml';
        }

        return $file;
    }
}
