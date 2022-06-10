<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Messages\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlComparison;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlJoinFactory;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingIO;
use CarloNicora\Minimalism\Services\Messaging\Data\DeletedMessages\Databases\DeletedMessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\Databases\MessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\DataObjects\Message;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\Databases\ParticipantsTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\IO\ParticipantIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\Databases\ThreadsTable;
use Exception;

class MessageIO extends AbstractMessagingIO
{
    /**
     * @param int $messageId
     * @return Message
     * @throws Exception
     */
    public function byMessageId(
        int $messageId,
    ): Message
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: MessagesTable::class)
                ->addParameter(field: MessagesTable::messageId, value: $messageId),
            responseType: Message::class,
        );
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @param int|null $fromMessageId
     * @return Message[]
     * @throws MinimalismException
     */
    public function byThreadId(
        int $threadId,
        int $userId,
        ?int $fromMessageId=null
    ): array
    {
        $queryFactory = SqlQueryFactory::create(tableClass: MessagesTable::class);
        $messageTable = $queryFactory->getTable();
        $participantsTable = SqlQueryFactory::create(tableClass: ParticipantsTable::class)->getTable();
        $deletedMessagesTable = SqlQueryFactory::create(tableClass: DeletedMessagesTable::class)->getTable();

        $sql = 'SELECT '
            . $messageTable->getField(field: MessagesTable::messageId)->getFullName() . ','
            . $messageTable->getField(field: MessagesTable::userId)->getFullName() . ','
            . $messageTable->getField(field: MessagesTable::content)->getFullName() . ','
            . $messageTable->getField(field: MessagesTable::createdAt)->getFullName() . ','
            . ' IF(' . $messageTable->getField(field: MessagesTable::createdAt)->getFullName() . '>=' .  $participantsTable->getField(ParticipantsTable::lastActivity)->getFullName() . ', 1, 0) as unread'
            . ' FROM ' . $messageTable->getFullName()
            . ' JOIN ' . $participantsTable->getFullName()
            . ' ON ' . $participantsTable->getField(field: ParticipantsTable::threadId)->getFullName() . '=?'
            . ' AND ' . $participantsTable->getField(field: ParticipantsTable::userId)->getFullName() . '=?'
            . ' WHERE ' . $messageTable->getField(field: MessagesTable::threadId)->getFullName() . '=?'
            . ' AND ' . $messageTable->getField(field: MessagesTable::messageId)->getFullName()
            . ' NOT IN ('
            . '  SELECT ' . $deletedMessagesTable->getField(field: DeletedMessagesTable::messageId)->getFullName()
            . '  FROM ' . $deletedMessagesTable->getFullName()
            . '  WHERE ' . $deletedMessagesTable->getField(field: DeletedMessagesTable::userId)->getFullName() . '=?'
            . ' )';
        $queryFactory->addParameter(field: ParticipantsTable::threadId, value: $threadId)
        ->addParameter(field: ParticipantsTable::userId, value: $userId)
        ->addParameter(field: MessagesTable::threadId, value: $threadId)
        ->addParameter(field: DeletedMessagesTable::userId, value: $userId);

        if ($fromMessageId !== null){
            $sql .= ' AND ' . $messageTable->getField(field: MessagesTable::messageId)->getFullName() . '<?';
            $queryFactory->addParameter(field: MessagesTable::messageId, value: $fromMessageId,comparison: SqlComparison::LesserThan);
        }

        $sql .= ' ORDER BY ' . $messageTable->getField(field: MessagesTable::createdAt)->getFullName() . ' DESC'
            . ' LIMIT 0,25;';

        $queryFactory->setSql($sql);

        return $this->data->read(
            queryFactory: $queryFactory,
            responseType: Message::class,
            requireObjectsList: true,
        );
    }

    /**
     * @param int $userId
     * @param int $lastChecked
     * @return Message[]
     * @throws MinimalismException
     */
    public function newByUserId(
        int $userId,
        int $lastChecked,
    ): array
    {
        $queryFactory = SqlQueryFactory::create(tableClass: MessagesTable::class)
            ->addJoin(join: SqlJoinFactory::create(primaryKey: MessagesTable::threadId, foreignKey: ThreadsTable::threadId))
            ->addJoin(join: SqlJoinFactory::create(primaryKey: ThreadsTable::threadId, foreignKey: ParticipantsTable::threadId))
            ->addParameter(field: ParticipantsTable::userId, value: $userId)
            ->addParameter(field: MessagesTable::userId, value: $userId, comparison: SqlComparison::NotEqual)
            ->addParameter(field: MessagesTable::createdAt, value: $lastChecked, comparison: SqlComparison::GreaterThan);

        return $this->data->read(
            queryFactory: $queryFactory,
            responseType: Message::class,
            requireObjectsList: true,
        );
    }

    /**
     * @param Message $message
     * @return int
     * @throws MinimalismException
     * @throws Exception
     */
    public function insert(
        Message $message,
    ): int
    {
        $newMessage = $this->data->create(
            queryFactory: $message,
            responseType: Message::class,
        );

        $this->objectFactory->create(className: ParticipantIO::class)->unarchiveThread($message->getThreadId());

        return $newMessage['messageId'];
    }

    /**
     * @param int $userId
     * @param int $messageId
     * @return void
     * @throws MinimalismException
     */
    public function deleteByUserIdMessageId(
        int $userId,
        int $messageId,
    ): void
    {
        /** @noinspection UnusedFunctionResultInspection */
        $this->data->create(
            queryFactory: SqlQueryFactory::create(DeletedMessagesTable::class)
                ->insert()
                ->addParameter(field: DeletedMessagesTable::userId, value: $userId)
                ->addParameter(field: DeletedMessagesTable::messageId, value: $messageId),
        );
    }
}