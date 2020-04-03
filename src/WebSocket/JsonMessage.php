<?php

namespace HuangYi\Shadowfax\WebSocket;

use HuangYi\Shadowfax\Contracts\WebSocket\Message;
use HuangYi\Shadowfax\Exceptions\InvalidMessageException;

class JsonMessage implements Message
{
    /**
     * The data.
     *
     * @var array
     */
    protected $data;

    /**
     * The raw data.
     *
     * @var string
     */
    protected $rawData;

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
        $this->rawData = $data;
        $this->opcode = $opcode;

        $this->parseJson($data);
    }

    /**
     * Parse json data.
     *
     * @param  string  $data
     * @throws \HuangYi\Shadowfax\Exceptions\InvalidMessageException
     */
    protected function parseJson($data)
    {
        $this->data = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidMessageException('Invalid json message.');
        }
    }

    /**
     * Get the data.
     *
     * @return array
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
        return $this->rawData;
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
