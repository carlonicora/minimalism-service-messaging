<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Threads\IO;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlComparison;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\Databases\MessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\Databases\ParticipantsTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\Databases\ThreadsTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\DataObjects\Thread;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlJoinFactory;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;

class ThreadIO extends AbstractMessagingIO
{

    /**
     * @param int $threadId
     * @return Thread
     * @throws MinimalismException
     */
    public function readByThreadId(
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
    public function readByMessageId(
        int $messageId
    ): Thread
    {
        // We use this method in notifier
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: ThreadsTable::class)
                ->addJoin(new SqlJoinFactory(primaryKey: ThreadsTable::threadId, foreignKey: MessagesTable::threadId))
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
    public function readByUserId(
        int $userId,
        ?int $fromTime=null
    ): array
    {
        $queryFactory = SqlQueryFactory::create(tableClass: ThreadsTable::class);
        $threadsTable = $queryFactory->getTable();
        $messagesTable = SqlQueryFactory::create(tableClass: MessagesTable::class)->getTable();
        $participantsTable = SqlQueryFactory::create(tableClass: ParticipantsTable::class)->getTable();

        $sql ='SELECT '
            . $threadsTable->getField(field: ThreadsTable::threadId)->getFullName() . ','
            . $messagesTable->getField(field: MessagesTable::createdAt)->getFullName() . ','
            . $messagesTable->getField(field: MessagesTable::content)->getFullName() . ','
            . ' count(msg.messageId) as unread'
            . ' FROM ' . $threadsTable->getFullName()
            . ' JOIN ' . $messagesTable->getFullName()
            . ' ON ' . $messagesTable->getField(field: MessagesTable::messageId)->getFullName() . '='
            . ' ('
            . '  SELECT'
            . '  ' . $messagesTable->getField(field: MessagesTable::messageId)->getFullName()
            . '  FROM ' . $messagesTable->getFullName()
            . '  WHERE ' . $messagesTable->getField(field: MessagesTable::threadId)->getFullName() . '='
            . '  ' . $threadsTable->getField(field: ThreadsTable::threadId)->getFullName()
            . '  ORDER BY ' . $messagesTable->getField(field: MessagesTable::createdAt)->getFullName() . ' DESC LIMIT 1'
            . ' )'
            . ' JOIN ' . $participantsTable->getFullName()
            . ' ON ' . $threadsTable->getField(field: ThreadsTable::threadId)->getFullName() . '=' . $participantsTable->getField(field: ParticipantsTable::threadId)->getFullName()
            . ' AND msg.userId!=?'
            . ' AND msg.createdAt>' . $participantsTable->getField(field: ParticipantsTable::lastActivity)->getFullName()
            . ' WHERE ' . $participantsTable->getField(field: ParticipantsTable::userId)->getFullName() . '=?'
            . ' AND ' . $participantsTable->getField(field: ParticipantsTable::isArchived)->getFullName() . '=?';
        $queryFactory->addParameter(field: 'msg.userId', value: $userId, stringParameterType: FieldType::Integer)
            ->addParameter(field: ParticipantsTable::userId, value: $userId)
            ->addParameter(field: ParticipantsTable::isArchived, value: 0);


        if ($fromTime !== null){
            $sql .= ' AND ' . $messagesTable->getField(field: MessagesTable::createdAt)->getFullName() . '<?';
            $queryFactory->addParameter(field: MessagesTable::createdAt, value: $fromTime, comparison: SqlComparison::LesserThan);
        }

        $sql .= ' GROUP BY '
            . $threadsTable->getField(field: ThreadsTable::threadId)->getFullName() . ','
            . $messagesTable->getField(field: MessagesTable::createdAt)->getFullName() . ','
            . $messagesTable->getField(field: MessagesTable::content)->getFullName()
            . ' ORDER BY ' . $messagesTable->getField(field: MessagesTable::createdAt)->getFullName() . ' DESC'
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
        $queryFactory = SqlQueryFactory::create(ThreadsTable::class)
            ->setSql(sql: 'SELECT'
                . ' participants.threadId'
                . ' FROM participants '
                . ' WHERE participants.threadId IN '
                . ' ( '
                . '    SELECT participants.threadId '
                . '    FROM participants'
                . '    WHERE participants.userId=? AND participants.threadId IN ('
                . '      SELECT participants.threadId'
                . '      FROM participants'
                . '      WHERE participants.userId=?'
                . '    )'
                . ' )'
                . ' GROUP BY participants.threadId '
                . ' HAVING count(participants.userId)=2;')
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