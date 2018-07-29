<?php

namespace HuangYi\Swoole\Contracts;

interface MessageContract
{
    /**
     * Get event.
     *
     * @return string
     */
    public function getEvent();

    /**
     * Get data.
     *
     * @return array|null
     */
    public function getData();

    /**
     * Get socket id.
     *
     * @return int
     */
    public function getSocketId();

    /**
     * Convert message to string.
     *
     * @return string
     */
    public function __toString();
}
