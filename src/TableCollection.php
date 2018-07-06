<?php

namespace HuangYi\Swoole;

use HuangYi\Swoole\Exceptions\TableCreationFailedException;
use HuangYi\Swoole\Exceptions\TableUndefinedException;
use Swoole\Table;

class TableCollection
{
    /**
     * @var array
     */
    protected $tables = [];

    /**
     * Column types.
     *
     * @var array
     */
    protected $types = [
        'int'     => Table::TYPE_INT,
        'integer' => Table::TYPE_INT,
        'string'  => Table::TYPE_STRING,
        'char'    => Table::TYPE_STRING,
        'varchar' => Table::TYPE_STRING,
        'float'   => Table::TYPE_FLOAT,
    ];

    /**
     * Table collection.
     *
     * @param array $tables
     * @return void
     * @throws \HuangYi\Swoole\Exceptions\TableCreationFailedException
     */
    public function __construct($tables = [])
    {
        foreach ($tables as $table) {
            $this->create($table);
        }
    }

    /**
     * Create table.
     *
     * @param array $config
     * @return \Swoole\Table
     * @throws \HuangYi\Swoole\Exceptions\TableCreationFailedException
     */
    public function create(array $config)
    {
        $name = $config['name'];
        $size = array_get($config, 'size', 1024);
        $columns = array_get($config, 'columns', []);

        $table = new Table($size);

        foreach ($columns as $column) {
            $this->createColumn($table, $column);
        }

        if (! $table->create()) {
            throw new TableCreationFailedException(
                "An error occurred while creating table [{$name}]."
            );
        }

        $this->tables[$name] = $table;

        return $table;
    }

    /**
     * Create column.
     *
     * @param \Swoole\Table $table
     * @param array $config
     * @return void
     */
    protected function createColumn(Table $table, array $config)
    {
        $name = $config[0];
        $type = $this->columnType($config[1]);
        $size = array_get($config, 2, 0);

        $table->column($name, $type, $size);
    }

    /**
     * Truncate table.
     *
     * @param string $name
     * @return bool
     */
    public function truncate($name)
    {
        if (! isset($this->tables[$name])) {
            return false;
        }

        foreach ($this->tables[$name] as $key => $row) {
            $this->tables[$name]->del($key);
        }

        return true;
    }

    /**
     * Transform table column type.
     *
     * @param string $type
     * @return int
     */
    protected function columnType($type)
    {
        if (in_array($type, $this->types)) {
            return (int) $type;
        }

        return array_get($this->types, $type, Table::TYPE_STRING);
    }

    /**
     * Change table.
     *
     * @param string $name
     * @return \Swoole\Table
     * @throws \HuangYi\Swoole\Exceptions\TableUndefinedException
     */
    public function use($name)
    {
        if (! isset($this->tables[$name])) {
            throw new TableUndefinedException(sprintf('Undefined Table [%s].', $name));
        }

        return $this->tables[$name];
    }
}
