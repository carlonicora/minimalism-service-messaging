<?php
namespace CarloNicora\Minimalism\Services\Messaging\Tests\Unit\Data\Databases\Messaging\Tables;

use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\ThreadsTable;
use CarloNicora\Minimalism\Services\Messaging\Tests\Abstracts\AbstractUnitTest;
use CarloNicora\Minimalism\Services\MySQL\Facades\SQLQueryCreationFacade;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\SQLFunctionsFacadeInterface;

class ThreadsTableTest extends AbstractUnitTest
{
    /** @var ThreadsTable  */
    private ThreadsTable $table;

    /**
     *
     */
    protected function setUp(): void
    {
        $this->table = $this->getMockBuilder(ThreadsTable::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                $this->getAllMethodNamesExcept(
                    ThreadsTable::class,
                    [
                        'getTableName',
                    ]
                )
            )
            ->getMock();

        $query = new SQLQueryCreationFacade(
            $this->getLogger(),
            $this->table
        );
        $this->setProperty($this->table, 'query', $query);

        $function = $this->getMockBuilder(SQLFunctionsFacadeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $function->method('runRead')
            ->willReturn([]);
        $this->setProperty($this->table, 'functions', $function);
    }

    /**
     *
     */
    public function testTableName(): void
    {
        $tableName = $this->getProperty(
            $this->table,
            'tableName'
        );

        self::assertEquals('threads', $tableName);
    }

    /**
     *
     */
    public function testTableFields(): void
    {
        $tableFields = $this->getProperty(
            $this->table,
            'fields'
        );

        self::assertArrayHasKey('threadId', $tableFields);
    }

    /**
     *
     */
    public function testTableDoesNotSupportInsertIgnore(): void
    {
        $tableInsertIgnore = $this->getProperty(
            $this->table,
            'insertIgnore'
        );

        self::assertEmpty($tableInsertIgnore);
    }
}