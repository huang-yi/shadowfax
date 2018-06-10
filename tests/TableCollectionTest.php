<?php

namespace HuangYi\Swoole\Tests;

use HuangYi\Swoole\Exceptions\TableUndefinedException;
use HuangYi\Swoole\TableCollection;
use PHPUnit\Framework\TestCase;
use Swoole\Table;

class TableCollectionTest extends TestCase
{
    /**
     * @var \HuangYi\Swoole\TableCollection
     */
    protected $tableCollection;

    public function setUp()
    {
        parent::setUp();

        $this->tableCollection = new TableCollection();
    }

    public function testCreate()
    {
        $table = $this->createUsersTableConfig();

        $this->assertInstanceOf(Table::class, $table);
    }

    public function testUse()
    {
        $this->createUsersTableConfig();
        $table = $this->tableCollection->use('users');

        $this->assertInstanceOf(Table::class, $table);
    }

    public function testUseUndefinedTable()
    {
        $this->expectException(TableUndefinedException::class);

        $this->tableCollection->use('undefined_table');
    }

    protected function createUsersTableConfig()
    {
        return $this->tableCollection->create([
            'name' => 'users',
            'size' => 1024,
            'columns' => [
                ['id', 'int', 8],
                ['nickname', 'string', 255],
                ['score', 'float'],
            ],
        ]);
    }
}
