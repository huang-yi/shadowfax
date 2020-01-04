<?php

use HuangYi\Shadowfax\Shadowfax;

if (! function_exists('shadowfax')) {
    /**
     * Get the entry from Shadowfax container.
     *
     * @param  string  $id
     * @return \HuangYi\Shadowfax\Shadowfax|mixed
     */
    function shadowfax($id = null)
    {
        if (is_null($id)) {
            return Shadowfax::getInstance();
        }

        return Shadowfax::make($id);
    }
}
