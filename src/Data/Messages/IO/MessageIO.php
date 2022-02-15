<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Messages\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlComparison;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingIO;
use CarloNicora\Minimalism\Services\Messaging\Data\DeletedMessages\Databases\DeletedMessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\Databases\MessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\DataObjects\Message;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\Databases\ParticipantsTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\IO\ParticipantIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\Databases\ThreadsTable;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlJoinFactory;
use Exception;

class MessageIO extends AbstractMessagingIO
{
    /**
     * @param int $messageId
     * @return Message
     * @throws Exception
     */
    public function readByMessageId(
        int $messageId,
    ): Message
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(MessagesTable::class)
                ->selectAll()
                ->addParameter(MessagesTable::messageId, $messageId),
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
    public function readByThreadId(
        int $threadId,
        int $userId,
        ?int $fromMessageId=null
    ): array
    {
        $queryFactory = SqlQueryFactory::create(MessagesTable::class);
        $messageTable = $queryFactory->getTable();
        $participantsTable = SqlQueryFactory::create(ParticipantsTable::class)->getTable();
        $deletedMessagesTable = SqlQueryFactory::create(DeletedMessagesTable::class)->getTable();

        $sql = 'SELECT '
            . $messageTable->getField(MessagesTable::messageId)->getFullName() . ','
            . $messageTable->getField(MessagesTable::userId)->getFullName() . ','
            . $messageTable->getField(MessagesTable::content)->getFullName() . ','
            . $messageTable->getField(MessagesTable::createdAt)->getFullName() . ','
            . ' IF(' . $messageTable->getField(MessagesTable::createdAt)->getFullName() . '>=' .  $participantsTable->getField(ParticipantsTable::lastActivity)->getFullName() . ', 1, 0) as unread'
            . ' FROM ' . $messageTable->getFullName()
            . ' JOIN ' . $participantsTable->getFullName()
            . ' ON ' . $participantsTable->getField(ParticipantsTable::threadId)->getFullName() . '=?'
            . ' AND ' . $participantsTable->getField(ParticipantsTable::userId)->getFullName() . '=?'
            . ' WHERE ' . $messageTable->getField(MessagesTable::threadId)->getFullName() . '=?'
            . ' AND ' . $messageTable->getField(MessagesTable::messageId)->getFullName()
            . ' NOT IN ('
            . '  SELECT ' . $deletedMessagesTable->getField(DeletedMessagesTable::messageId)->getFullName()
            . '  FROM ' . $deletedMessagesTable->getFullName()
            . '  WHERE ' . $deletedMessagesTable->getField(DeletedMessagesTable::userId)->getFullName() . '=?'
            . ' )';
        $queryFactory->addParameter(ParticipantsTable::threadId, $threadId)
        ->addParameter(ParticipantsTable::userId, $userId)
        ->addParameter(MessagesTable::threadId, $threadId)
        ->addParameter(DeletedMessagesTable::userId, $userId);

        if ($fromMessageId !== null){
            $sql .= ' AND ' . $messageTable->getField(MessagesTable::messageId)->getFullName() . '<?';
            $queryFactory->addParameter(field: MessagesTable::messageId, value: $fromMessageId,comparison: SqlComparison::LesserThan);
        }

        $sql .= ' ORDER BY ' . $messageTable->getField(MessagesTable::createdAt)->getFullName() . ' DESC'
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
    public function readNewByUserId(
        int $userId,
        int $lastChecked,
    ): array
    {
        $queryFactory = SqlQueryFactory::create(MessagesTable::class)
            ->selectAll()
            ->addJoin(new SqlJoinFactory(MessagesTable::threadId, ThreadsTable::threadId))
            ->addJoin(new SqlJoinFactory(ThreadsTable::threadId, ParticipantsTable::threadId))
            ->addParameter(ParticipantsTable::userId, $userId)
            ->addParameter(MessagesTable::userId, $userId, SqlComparison::NotEqual)
            ->addParameter(MessagesTable::createdAt, $lastChecked, SqlComparison::GreaterThan);

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
    public function create(
        Message $message,
    ): int
    {
        $newMessage = $this->data->create(
            queryFactory: $message,
            responseType: Message::class,
        );

        $this->objectFactory->create(ParticipantIO::class)->unarchiveThread($message->getThreadId());

        return $newMessage['messageId'];
    }

    /**
     * @param int $userId
     * @param int $messageId
     * @return void
     * @throws MinimalismException
     */
    public function delete(
        int $userId,
        int $messageId,
    ): void
    {
        /** @noinspection UnusedFunctionResultInspection */
        $this->data->create(
            queryFactory: SqlQueryFactory::create(DeletedMessagesTable::class)
                ->insert()
                ->addParameter(DeletedMessagesTable::userId, $userId)
                ->addParameter(DeletedMessagesTable::messageId, $messageId),
        );
    }
}