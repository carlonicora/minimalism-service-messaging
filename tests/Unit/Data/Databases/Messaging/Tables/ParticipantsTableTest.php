<?php
namespace CarloNicora\Minimalism\Services\Messaging\Tests\Unit\Data\Databases\Messaging\Tables;

use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\ParticipantsTable;
use CarloNicora\Minimalism\Services\Messaging\Tests\Abstracts\AbstractUnitTest;
use CarloNicora\Minimalism\Services\MySQL\Facades\SQLQueryCreationFacade;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\SQLFunctionsFacadeInterface;
use Exception;

class ParticipantsTableTest extends AbstractUnitTest
{
    /** @var ParticipantsTable  */
    private ParticipantsTable $table;

    /**
     *
     */
    protected function setUp(): void
    {
        $this->table = $this->getMockBuilder(ParticipantsTable::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                $this->getAllMethodNamesExcept(
                    ParticipantsTable::class,
                    [
                        'getTableName',
                        'readByThreadId'
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

        self::assertEquals('participants', $tableName);
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
        self::assertArrayHasKey('userId', $tableFields);
        self::assertArrayHasKey('isArchived', $tableFields);
        self::assertArrayHasKey('lastActivity', $tableFields);
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

    /**
     * @throws Exception
     */
    public function testReadByThreadId():void
    {
        /** @noinspection UnusedFunctionResultInspection */
        $this->table->readByThreadId(
            threadId:1,
        );

        $sql = $this->getSql($this->table);
        $parameters = $this->getParameters($this->table);
        self::assertEquals(
            'SELECT userId FROM participants WHERE threadId=?;',
            $sql
        );
        self::assertEquals('i', $parameters[0]);
        self::assertCount(2, $parameters);
    }
}