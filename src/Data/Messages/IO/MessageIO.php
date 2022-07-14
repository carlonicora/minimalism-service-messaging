<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Messages\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlComparison;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlFieldFactory;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlJoinFactory;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlTableFactory;
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
        int  $threadId,
        int  $userId,
        ?int $fromMessageId = null
    ): array
    {
        $queryFactory = SqlQueryFactory::create(tableClass: MessagesTable::class);

        $messages          = SqlTableFactory::create(tableClass: MessagesTable::class)->getFullName();
        $messagesMessageId = SqlFieldFactory::create(field: MessagesTable::messageId)->getFullName();
        $messagesThreadId  = SqlFieldFactory::create(field: MessagesTable::threadId)->getFullName();
        $messagesUserId    = SqlFieldFactory::create(field: MessagesTable::userId)->getFullName();
        $messagesContent   = SqlFieldFactory::create(field: MessagesTable::content)->getFullName();
        $messagesCreatedAt = SqlFieldFactory::create(field: MessagesTable::createdAt)->getFullName();

        $participants             = SqlTableFactory::create(tableClass: ParticipantsTable::class)->getFullName();
        $participantsLastActivity = SqlFieldFactory::create(field: ParticipantsTable::lastActivity)->getFullName();
        $participantsThreadId     = SqlFieldFactory::create(field: ParticipantsTable::threadId)->getFullName();
        $participantsUserId       = SqlFieldFactory::create(field: ParticipantsTable::userId)->getFullName();

        $deletedMessages          = SqlTableFactory::create(tableClass: DeletedMessagesTable::class)->getFullName();
        $deletedMessagesMessageId = SqlFieldFactory::create(field: DeletedMessagesTable::messageId)->getFullName();
        $deletedMessagesUserId    = SqlFieldFactory::create(field: DeletedMessagesTable::userId)->getFullName();

        $sql = 'SELECT ' . $messagesMessageId . ',' . $messagesThreadId . ',' . $messagesUserId . ',' . $messagesContent . ',' . $messagesCreatedAt . ','
            . '   IF(' . $messagesCreatedAt . '>=' . $participantsLastActivity . ', 1, 0) as unread'
            . ' FROM ' . $messages
            . '   JOIN ' . $participants . ' ON ' . $participantsThreadId . '=?' . ' AND ' . $participantsUserId . '=?'
            . ' WHERE ' . $messagesThreadId . '=?'
            . '   AND ' . $messagesMessageId
            . '     NOT IN ('
            . '       SELECT ' . $deletedMessagesMessageId
            . '       FROM ' . $deletedMessages
            . '       WHERE ' . $deletedMessagesUserId . '=?'
            . '     )';

        $queryFactory->addParameter(field: ParticipantsTable::threadId, value: $threadId)
            ->addParameter(field: ParticipantsTable::userId, value: $userId)
            ->addParameter(field: MessagesTable::threadId, value: $threadId)
            ->addParameter(field: DeletedMessagesTable::userId, value: $userId);

        if ($fromMessageId !== null) {
            $sql .= ' AND ' . $messagesMessageId . '<?';
            $queryFactory->addParameter(field: MessagesTable::messageId, value: $fromMessageId, comparison: SqlComparison::LesserThan);
        }

        $sql .= ' ORDER BY ' . $messagesCreatedAt . ' DESC'
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
     * @param $userId
     * @return bool
     * @throws MinimalismException
     */
    public function doesUserHasUnreadMessages(
        $userId
    ): bool
    {
        $messages = SqlTableFactory::create(tableClass: MessagesTable::class)->getFullName();
        $messagesMessageId = SqlFieldFactory::create(field: MessagesTable::messageId)->getFullName();
        $messagesUserId = SqlFieldFactory::create(field: MessagesTable::userId)->getFullName();
        $messagesThreadId = SqlFieldFactory::create(field: MessagesTable::threadId)->getFullName();
        $messagesCreatedAt = SqlFieldFactory::create(field: MessagesTable::createdAt)->getFullName();

        $participants = SqlTableFactory::create(tableClass: ParticipantsTable::class)->getFullName();
        $participantsThreadId = SqlFieldFactory::create(field: ParticipantsTable::threadId)->getFullName();
        $participantsUserId = SqlFieldFactory::create(field: ParticipantsTable::userId)->getFullName();
        $participantsLastActivity = SqlFieldFactory::create(field: ParticipantsTable::lastActivity)->getFullName();
        $participantIsArchived = SqlFieldFactory::create(field: ParticipantsTable::isArchived)->getFullName();

        $deletedMessages = SqlTableFactory::create(tableClass: DeletedMessagesTable::class)->getFullName();
        $deletedMessagesMessageId = SqlFieldFactory::create(DeletedMessagesTable::messageId)->getFullName();
        $deletedMessagesUserId = SqlFieldFactory::create(DeletedMessagesTable::userId)->getFullName();

        $sql = ' SELECT ' . $messagesMessageId
            . ' FROM ' . $messages
            . ' JOIN ' . $participants . ' ON ' . $participantsThreadId . '=' . $messagesThreadId
            . ' WHERE ' . $participantsUserId . '=?'
            . '   AND ' . $participantIsArchived . '=0'
            . '   AND ' . $messagesUserId . '!=?'
            . '   AND ' . $messagesCreatedAt . '>=' . $participantsLastActivity
            . '   AND ' . $messagesMessageId . ' NOT IN ('
            . '     SELECT ' . $deletedMessagesMessageId
            . '     FROM ' . $deletedMessages
            . '     WHERE ' . $deletedMessagesUserId . '=' . $messagesUserId
            . '   )'
            . ' LIMIT 0,1';

        $result = $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: MessagesTable::class)
                ->setSql($sql)
                ->addParameter(field: ParticipantsTable::userId, value: $userId)
                ->addParameter(field: MessagesTable::userId, value: $userId),
        );

        return ! empty($result);
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

        return $newMessage->getId();
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
        $this->data->delete(
            queryFactory: SqlQueryFactory::create(DeletedMessagesTable::class)
                ->delete()
                ->addParameter(field: DeletedMessagesTable::userId, value: $userId)
                ->addParameter(field: DeletedMessagesTable::messageId, value: $messageId),
        );
    }
}