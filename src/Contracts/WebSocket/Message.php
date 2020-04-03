<?php

namespace HuangYi\Shadowfax\Contracts\WebSocket;

interface Message
{
    /**
     * Create a new Message.
     *
     * @param  string  $data
     * @param  int  $opcode
     * @return void
     */
    public function __construct($data, $opcode);

    /**
     * Get the data.
     *
     * @return mixed
     */
    public function getData();

    /**
     * Get the raw data.
     *
     * @return string
     */
    public function getRawData();

    /**
     * Get the opcode.
     *
     * @return int
     */
    public function getOpcode();
}
