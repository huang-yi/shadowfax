<?php

namespace HuangYi\Shadowfax\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class EntryNotFoundException extends ShadowfaxException implements NotFoundExceptionInterface
{
    public function __construct($id)
    {
        parent::__construct("The [$id] was not found in the container.");
    }
}
