<?php
namespace CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables;

use CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables\Enums\ParticipantStatus;
use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class ParticipantsTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'participants';

    /** @var array  */
    protected static array $fields = [
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
        $this->sql = 'SELECT userId, isArchived, lastActivity FROM participants WHERE threadId=?;';
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
        $this->parameters = ['iii', ParticipantStatus::Archived->value, $threadId, $userId];

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
        $this->parameters = ['ii', ParticipantStatus::Active->value, $threadId];

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