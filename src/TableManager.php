<?php

namespace HuangYi\Http;

use Swoole\Table;

class TableManager
{
    /**
     * @var array
     */
    protected $tables = [];

    /**
     * TableManager.
     *
     * @param array
     * @return void
     */
    public function __construct($tables)
    {
        foreach ($tables as $table) {
            $this->create($table);
        }
    }

    /**
     * Create table.
     *
     * @param array $config
     * @return bool
     */
    protected function create(array $config)
    {
        $name = $config['name'];
        $table = new Table($config['size'] ?? 1024);

        if (isset($config['columns'])) {
            foreach ($config['columns'] as $column) {
                $columnName = $column[0];
                $columnType = $this->mapColumnType($column[1]);
                $columnSize = $column[2] ?? 0;

                $table->column($columnName, $columnType, $columnSize);
            }
        }

        if ($created = $table->create()) {
            $this->tables[$name] = $table;
        }

        return $created;
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
     * Map table column type.
     *
     * @param string $type
     * @return int
     */
    protected function mapColumnType($type)
    {
        $map = [
            'int' => Table::TYPE_INT,
            'integer' => Table::TYPE_INT,
            'string' => Table::TYPE_STRING,
            'char' => Table::TYPE_STRING,
            'varchar' => Table::TYPE_STRING,
            'float' => Table::TYPE_FLOAT,
        ];

        return $map[$type] ?? Table::TYPE_STRING;
    }

    /**
     * Change table.
     *
     * @param string $name
     * @return \Swoole\Table|null
     */
    public function use($name)
    {
        return $this->tables[$name] ?? null;
    }
}
