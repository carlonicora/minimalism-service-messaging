<?php
namespace CarloNicora\Minimalism\Services\Messaging\Tests\Unit\Data\Databases\Messaging\Tables;

use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\MessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Tests\Abstracts\AbstractUnitTest;
use CarloNicora\Minimalism\Services\MySQL\Facades\SQLQueryCreationFacade;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\SQLFunctionsFacadeInterface;

class MessagesTableTest extends AbstractUnitTest
{
    /** @var MessagesTable  */
    private MessagesTable $table;

    /**
     *
     */
    protected function setUp(): void
    {
        $this->table = $this->getMockBuilder(MessagesTable::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                $this->getAllMethodNamesExcept(
                    MessagesTable::class,
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

        self::assertEquals('messages', $tableName);
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

        self::assertArrayHasKey('messageId', $tableFields);
        self::assertArrayHasKey('threadId', $tableFields);
        self::assertArrayHasKey('userId', $tableFields);
        self::assertArrayHasKey('content', $tableFields);
        self::assertArrayHasKey('createdAt', $tableFields);
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