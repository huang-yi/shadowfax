<?php

namespace HuangYi\Shadowfax\Bootstrap;

use HuangYi\Shadowfax\Shadowfax;

class RegisterComponents
{
    /**
     * Register the components.
     *
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @return void
     */
    public function bootstrap(Shadowfax $shadowfax)
    {
        $components = (array) $shadowfax['config']->get('components', []);

        foreach ($components as $component) {
            $shadowfax->register($component);
        }
    }
}
