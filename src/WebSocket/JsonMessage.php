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
     * @throws \HuangYi\Shadowfax\Exceptions\InvalidMessageException
     */
    public function __construct($data, $opcode = WEBSOCKET_OPCODE_TEXT)
    {
        if ($opcode != WEBSOCKET_OPCODE_TEXT) {
            throw new InvalidMessageException('Only support text frame.', 1003);
        }

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
            throw new InvalidMessageException('Only support json payload.', 1007);
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
