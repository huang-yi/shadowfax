<?php

namespace HuangYi\Shadowfax\Exceptions;

class InstanceNotFoundException extends ShadowfaxException
{
    public function __construct($abstract)
    {
        parent::__construct("The instance [$abstract] was not found in the container.");
    }
}
