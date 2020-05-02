<?php

namespace HuangYi\Shadowfax\WebSocket;

use HuangYi\Shadowfax\Contracts\WebSocket\Message;

class RawMessage implements Message
{
    /**
     * The data.
     *
     * @var string
     */
    protected $data;

    /**
     * The opcode.
     *
     * @var int
     */
    protected $opcode;

    /**
     * Create a new message.
     *
     * @param  string  $data
     * @param  int  $opcode
     * @return void
     */
    public function __construct($data, $opcode)
    {
        $this->data = $data;
        $this->opcode = $opcode;
    }

    /**
     * Get the data.
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the raw data.
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->data;
    }

    /**
     * Get the opcode.
     *
     * @return int
     */
    public function getOpcode()
    {
        return $this->opcode;
    }
}
