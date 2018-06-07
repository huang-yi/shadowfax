<?php

namespace HuangYi\Swoole\Tasks;

use HuangYi\Swoole\Contracts\TaskContract;

abstract class TaskAbstract implements TaskContract
{
    /**
     * Task data.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Make a new task.
     *
     * @param mixed $data
     * @return static
     */
    public static function make($data = null)
    {
        return new static($data);
    }

    /**
     * Task Abstract.
     *
     * @param mixed $data
     * @return void
     */
    public function __construct($data = null)
    {
        $this->data = $data;
    }
}
