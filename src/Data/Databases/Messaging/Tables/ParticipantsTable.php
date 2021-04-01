<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class ParticipantsTable extends AbstractMySqlTable
{
    /** @var string */
    protected string $tableName = 'participants';

    /** @var array  */
    protected array $fields = [
        'threadId'      => FieldInterface::INTEGER
                        +  FieldInterface::PRIMARY_KEY,
        'userId'        => FieldInterface::STRING
                        +  FieldInterface::PRIMARY_KEY,
        'isArchived'    => FieldInterface::INTEGER,
        'lastActivity'  => FieldInterface::STRING
    ];

    /**
     * @param int $threadId
     * @return array
     * @throws Exception
     */
    public function readByThreadId(
        int $threadId
    ): array
    {
        $this->sql = 'SELECT userId FROM participants WHERE threadId=?;';
        $this->parameters = ['i', $threadId];

        return $this->functions->runRead();
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @throws Exception
     */
    public function updateThreadArchived(
        int $threadId,
        int $userId,
    ): void
    {
        $this->sql = 'UPDATE participants SET isArchived=? WHERE threadId=? AND userId=?;';
        $this->parameters = ['iii', true, $threadId, $userId];

        $this->functions->runSql();
    }

    /**
     * @param int $threadId
     * @throws Exception
     */
    public function updateThreadUnarchived(
        int $threadId,
    ): void
    {
        $this->sql = 'UPDATE participants SET isArchived=? WHERE threadId=?;';
        $this->parameters = ['ii', false, $threadId];

        $this->functions->runSql();
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @throws Exception
     */
    public function updateThreadAsRead(
        int $threadId,
        int $userId,
    ): void
    {
        $this->sql = 'UPDATE participants SET lastActivity=? WHERE threadId=? AND userId=?;';
        $this->parameters = ['sii', date('Y-m-d H:i:s'), $threadId, $userId];

        $this->functions->runSql();
    }
}