<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Threads\IO;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlComparison;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlFieldFactory;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlJoinFactory;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\Databases\MessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\Databases\ParticipantsTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\Databases\ThreadsTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\DataObjects\Thread;

class ThreadIO extends AbstractMessagingIO
{

    /**
     * @param int $threadId
     * @return Thread
     * @throws MinimalismException
     */
    public function byThreadId(
        int $threadId
    ): Thread
    {
        // We use this method in notifier
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: ThreadsTable::class)
                ->addParameter(field: ThreadsTable::threadId, value: $threadId),
            responseType: Thread::class
        );
    }

    /**
     * @param int $messageId
     * @return Thread
     * @throws MinimalismException
     */
    public function byMessageId(
        int $messageId
    ): Thread
    {
        // We use this method in notifier
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: ThreadsTable::class)
                ->addJoin(SqlJoinFactory::create(primaryKey: ThreadsTable::threadId, foreignKey: MessagesTable::threadId))
                ->addParameter(field: MessagesTable::messageId, value: $messageId),
            responseType: Thread::class
        );
    }

    /**
     * @param int $userId
     * @param int|null $fromTime
     * @return array
     * @throws MinimalismException
     */
    public function byUserId(
        int $userId,
        ?int $fromTime=null
    ): array
    {
        $queryFactory    = SqlQueryFactory::create(tableClass: ThreadsTable::class);
        $threadsTable    = $queryFactory->getTable()->getFullName();
        $threadsThreadId = SqlFieldFactory::create(field: ThreadsTable::threadId)->getFullName();

        $messagesTable     = SqlQueryFactory::create(tableClass: MessagesTable::class)->getTable()->getFullName();
        $messagesMessageId = SqlFieldFactory::create(field: MessagesTable::messageId)->getFullName();
        $messagesContent   = SqlFieldFactory::create(field: MessagesTable::content)->getFullName();
        $messagesThreadId  = SqlFieldFactory::create(field: MessagesTable::threadId)->getFullName();
        $messagesCreatedAt = SqlFieldFactory::create(field: MessagesTable::createdAt)->getFullName();

        $participantsTable         = SqlQueryFactory::create(tableClass: ParticipantsTable::class)->getTable()->getFullName();
        $participantsThreadId      = SqlFieldFactory::create(field: ParticipantsTable::threadId)->getFullName();
        $participantsLastActivitiy = SqlFieldFactory::create(field: ParticipantsTable::lastActivity)->getFullName();
        $participantsUserId        = SqlFieldFactory::create(field: ParticipantsTable::userId)->getFullName();
        $participantsIsArchived    = SqlFieldFactory::create(field: ParticipantsTable::isArchived)->getFullName();

        $msgsTable = 'msgs';
        $msgsUserId = SqlFieldFactory::create(field: MessagesTable::userId)->getName();

        $sql      = 'SELECT ' . $threadsThreadId . ',' . $messagesCreatedAt . ',' . $messagesContent . ','
            . '   COUNT(' . $msgsTable . '.' . SqlFieldFactory::create(field: MessagesTable::messageId)->getName() . ') AS unread'
            . ' FROM ' . $threadsTable
            . ' JOIN ' . $messagesTable . ' ON ' . $messagesMessageId . '='
            . ' ('
            . '  SELECT ' . $messagesMessageId
            . '  FROM ' . $messagesTable
            . '  WHERE ' . $messagesThreadId . '=' . '  ' . $threadsThreadId
            . '  ORDER BY ' . $messagesCreatedAt . ' DESC'
            . '  LIMIT 1'
            . ' )'
            . ' JOIN ' . $participantsTable . ' ON ' . $threadsThreadId . '=' . $participantsThreadId
            . ' LEFT JOIN ' . $messagesTable . ' ' . $msgsTable . ' ON ' . $msgsTable . '.' . SqlFieldFactory::create(field: MessagesTable::threadId)->getName() . ' = ' . $threadsThreadId
            . '   AND ' . $msgsTable . '.' . $msgsUserId . ' !=?'
            . '   AND ' . $msgsTable . '.' . SqlFieldFactory::create(field: MessagesTable::createdAt)->getName() . ' >' . $participantsLastActivitiy
            . ' WHERE ' . $participantsUserId . '=? AND ' . $participantsIsArchived . '=?';

        $queryFactory->addParameter(field: $msgsTable . '.' . $msgsUserId, value: $userId, stringParameterType: SqlFieldType::Integer)
            ->addParameter(field: ParticipantsTable::userId, value: $userId)
            ->addParameter(field: ParticipantsTable::isArchived, value: 0);

        if ($fromTime !== null){
            $sql .= ' AND ' . $messagesCreatedAt . '<?';
            $queryFactory->addParameter(field: MessagesTable::createdAt, value: $fromTime, comparison: SqlComparison::LesserThan);
        }

        $sql .= ' GROUP BY ' . $threadsThreadId . ',' . $messagesCreatedAt . ',' . $messagesContent
            . ' ORDER BY ' . $messagesCreatedAt . ' DESC'
            . ' LIMIT 0,25;';

        $queryFactory->setSql($sql);

        return $this->data->read(
            queryFactory: $queryFactory,
        );
    }

    /**
     * @param int $userId
     * @return int
     * @throws MinimalismException
     */
    public function countByUserId(
        int $userId
    ): int
    {
        $queryFactory = SqlQueryFactory::create(tableClass: ThreadsTable::class);
        $threadsTable = $queryFactory->getTable();
        $messagesTable = SqlQueryFactory::create(tableClass: MessagesTable::class)->getTable();
        $participantsTable = SqlQueryFactory::create(tableClass: ParticipantsTable::class)->getTable();

        $sql = 'SELECT count(' . $threadsTable->getField(field: ThreadsTable::threadId)->getFullName() . ') as counter'
            . ' FROM ' . $threadsTable->getFullName()
            . ' JOIN ' . $participantsTable->getFullName()
            . ' ON ' . $threadsTable->getField(field: ThreadsTable::threadId)->getFullName() . '=' . $participantsTable->getField(field: ParticipantsTable::threadId)->getFullName()
            . ' AND ' . $participantsTable->getField(field: ParticipantsTable::userId)->getFullName() . '=?'
            . ' JOIN ' . $messagesTable->getFullName()
            . ' ON ' . $messagesTable->getField(field: MessagesTable::messageId)->getFullName() . '='
            . ' ('
            . '  SELECT ' . $messagesTable->getField(field: MessagesTable::messageId)->getFullName()
            . '  FROM ' . $messagesTable->getFullName()
            . '  WHERE ' . $messagesTable->getField(field: MessagesTable::threadId)->getFullName() . '=' . $threadsTable->getField(field: ThreadsTable::threadId)->getFullName()
            . '  ORDER BY ' . $messagesTable->getField(field: MessagesTable::createdAt)->getFullName() . ' DESC LIMIT 1'
            . ' )'
            . ' WHERE ' . $messagesTable->getField(field: MessagesTable::createdAt)->getFullName() . '>=' . $participantsTable->getField(field: ParticipantsTable::lastActivity)->getFullName() . ';';
        $queryFactory->setSql($sql)
            ->addParameter(field: ParticipantsTable::userId, value: $userId);

        $recordset = $this->data->read(
            queryFactory: $queryFactory
        );

        if (array_key_exists(key: 1, array: $recordset)){
            throw new MinimalismException(status: HttpCode::InternalServerError, message: 'Error in count query');
        }

        return $recordset[0]['counter'];
    }

    /**
     * @param int $userId1
     * @param int $userId2
     * @return Thread
     * @throws MinimalismException
     */
    public function getDialogThread(
        int $userId1,
        int $userId2
    ): Thread
    {
        $participantsTable    = SqlQueryFactory::create(tableClass: ParticipantsTable::class)->getTable()->getFullName();
        $participantsThreadId = SqlFieldFactory::create(field: ParticipantsTable::threadId)->getFullName();
        $participantsUserId   = SqlFieldFactory::create(field: ParticipantsTable::userId)->getFullName();

        $queryFactory = SqlQueryFactory::create(ThreadsTable::class)
            ->setSql(sql: 'SELECT ' . $participantsThreadId
                . ' FROM ' . $participantsTable
                . ' WHERE ' . $participantsThreadId . ' IN '
                . ' ( '
                . '    SELECT ' . $participantsThreadId
                . '    FROM ' . $participantsTable
                . '    WHERE ' . $participantsUserId . '=? AND ' . $participantsThreadId . ' IN ('
                . '      SELECT ' . $participantsThreadId
                . '      FROM ' . $participantsTable
                . '      WHERE ' . $participantsUserId . '=?'
                . '    )'
                . ' )'
                . ' GROUP BY ' . $participantsThreadId
                . ' HAVING COUNT(' . $participantsUserId . ')=2;')
            ->addParameter(field: ParticipantsTable::userId, value: $userId1)
            ->addParameter(field: ParticipantsTable::userId, value: $userId2);

        return $this->data->read(
            queryFactory: $queryFactory,
            responseType: Thread::class,
        );
    }

    /**
     * @param array $userIds
     * @return int
     * @throws MinimalismException
     */
    public function insert(
        array $userIds
    ): int
    {
        $thread = new Thread();
        /** @var Thread $thread */
        $thread = $this->data->create(
            queryFactory: $thread,
        );

        $participants = [];

        foreach ($userIds as $userId){
            $participants[] = SqlQueryFactory::create(tableClass: ParticipantsTable::class)
                ->insert()
                ->addParameter(field: ParticipantsTable::threadId, value: $thread->getId())
                ->addParameter(field: ParticipantsTable::userId, value: $userId)
                ->addParameter(field: ParticipantsTable::isArchived, value: 0)
                ->addParameter(field: ParticipantsTable::lastActivity, value: time());
        }

        /** @noinspection UnusedFunctionResultInspection */
        $this->data->create(
            queryFactory: $participants,
        );

        return $thread->getId();
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @throws MinimalismException
     */
    public function archive(
        int $threadId,
        int $userId
    ): void
    {
        $queryFactory = SqlQueryFactory::create(tableClass: ParticipantsTable::class)
            ->update()
            ->addParameter(field: ParticipantsTable::threadId, value: $threadId)
            ->addParameter(field: ParticipantsTable::userId, value: $userId)
            ->addParameter(field: ParticipantsTable::isArchived, value: true);

        $this->data->update(
            queryFactory: $queryFactory,
        );
    }

    /**
     * @param int $userId
     * @param int $threadId
     * @throws MinimalismException
     */
    public function markAsRead(
        int $userId,
        int $threadId,
    ): void
    {
        $queryFactory = SqlQueryFactory::create(tableClass: ParticipantsTable::class)
            ->update()
            ->addParameter(field: ParticipantsTable::threadId, value: $threadId)
            ->addParameter(field: ParticipantsTable::userId, value: $userId)
            ->addParameter(field: ParticipantsTable::lastActivity, value: time());

        $this->data->update(
            queryFactory: $queryFactory,
        );
    }
}