<?php

namespace HuangYi\Shadowfax;

abstract class Component
{
    /**
     * The Shadowfax instance.
     *
     * @var \HuangYi\Shadowfax\Shadowfax
     */
    protected $shadowfax;

    /**
     * Create a new Component instance.
     *
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @return void
     */
    public function __construct(Shadowfax $shadowfax)
    {
        $this->shadowfax = $shadowfax;
    }

    /**
     * Register the component.
     *
     * @return void
     */
    abstract public function register();
}
