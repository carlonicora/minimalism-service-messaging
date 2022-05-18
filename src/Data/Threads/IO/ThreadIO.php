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
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;

class ThreadIO extends AbstractMessagingIO
{
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
        $queryFactory = SqlQueryFactory::create(ThreadsTable::class);
        $threadsTable = $queryFactory->getTable();
        $messagesTable = SqlQueryFactory::create(MessagesTable::class)->getTable();
        $participantsTable = SqlQueryFactory::create(ParticipantsTable::class)->getTable();

        $sql ='SELECT '
            . $threadsTable->getField(ThreadsTable::threadId)->getFullName() . ','
            . $messagesTable->getField(MessagesTable::createdAt)->getFullName() . ','
            . $messagesTable->getField(MessagesTable::content)->getFullName() . ','
            . ' count(msg.messageId) as unread'
            . ' FROM ' . $threadsTable->getFullName()
            . ' JOIN ' . $messagesTable->getFullName()
            . ' ON ' . $messagesTable->getField(MessagesTable::messageId)->getFullName() . '='
            . ' ('
            . '  SELECT'
            . '  ' . $messagesTable->getField(MessagesTable::messageId)->getFullName()
            . '  FROM ' . $messagesTable->getFullName()
            . '  WHERE ' . $messagesTable->getField(MessagesTable::threadId)->getFullName() . '='
            . '  ' . $threadsTable->getField(ThreadsTable::threadId)->getFullName()
            . '  ORDER BY ' . $messagesTable->getField(MessagesTable::createdAt)->getFullName() . ' DESC LIMIT 1'
            . ' )'
            . ' JOIN ' . $participantsTable->getFullName()
            . ' ON ' . $threadsTable->getField(ThreadsTable::threadId)->getFullName() . '=' . $participantsTable->getField(ParticipantsTable::threadId)->getFullName()
            . ' AND msg.userId!=?'
            . ' AND msg.createdAt>' . $participantsTable->getField(ParticipantsTable::lastActivity)->getFullName()
            . ' WHERE ' . $participantsTable->getField(ParticipantsTable::userId)->getFullName() . '=?'
            . ' AND ' . $participantsTable->getField(ParticipantsTable::isArchived)->getFullName() . '=?';
        $queryFactory->addParameter(field: 'msg.userId', value: $userId, stringParameterType: FieldType::Integer)
            ->addParameter(ParticipantsTable::userId, $userId)
            ->addParameter(ParticipantsTable::isArchived, 0);


        if ($fromTime !== null){
            $sql .= ' AND ' . $messagesTable->getField(MessagesTable::createdAt)->getFullName() . '<?';
            $queryFactory->addParameter(MessagesTable::createdAt, $fromTime,SqlComparison::LesserThan);
        }

        $sql .= ' GROUP BY '
            . $threadsTable->getField(ThreadsTable::threadId)->getFullName() . ','
            . $messagesTable->getField(MessagesTable::createdAt)->getFullName() . ','
            . $messagesTable->getField(MessagesTable::content)->getFullName()
            . ' ORDER BY ' . $messagesTable->getField(MessagesTable::createdAt)->getFullName() . ' DESC'
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
        $queryFactory = SqlQueryFactory::create(ThreadsTable::class);
        $threadsTable = $queryFactory->getTable();
        $messagesTable = SqlQueryFactory::create(MessagesTable::class)->getTable();
        $participantsTable = SqlQueryFactory::create(ParticipantsTable::class)->getTable();

        $sql = 'SELECT count(' . $threadsTable->getField(ThreadsTable::threadId)->getFullName() . ') as counter'
            . ' FROM ' . $threadsTable->getFullName()
            . ' JOIN ' . $participantsTable->getFullName()
            . ' ON ' . $threadsTable->getField(ThreadsTable::threadId)->getFullName() . '=' . $participantsTable->getField(ParticipantsTable::threadId)->getFullName()
            . ' AND ' . $participantsTable->getField(ParticipantsTable::userId)->getFullName() . '=?'
            . ' JOIN ' . $messagesTable->getFullName()
            . ' ON ' . $messagesTable->getField(MessagesTable::messageId)->getFullName() . '='
            . ' ('
            . '  SELECT ' . $messagesTable->getField(MessagesTable::messageId)->getFullName()
            . '  FROM ' . $messagesTable->getFullName()
            . '  WHERE ' . $messagesTable->getField(MessagesTable::threadId)->getFullName() . '=' . $threadsTable->getField(ThreadsTable::threadId)->getFullName()
            . '  ORDER BY ' . $messagesTable->getField(MessagesTable::createdAt)->getFullName() . ' DESC LIMIT 1'
            . ' )'
            . ' WHERE ' . $messagesTable->getField(MessagesTable::createdAt)->getFullName() . '>=' . $participantsTable->getField(ParticipantsTable::lastActivity)->getFullName() . ';';
        $queryFactory->setSql($sql)
            ->addParameter(ParticipantsTable::userId, $userId);

        $recordset = $this->data->read(
            queryFactory: $queryFactory
        );

        if (array_key_exists(1, $recordset)){
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
            ->setSql('SELECT'
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
            ->addParameter(ParticipantsTable::userId, $userId1)
            ->addParameter(ParticipantsTable::userId, $userId2);

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
            $participants[] = SqlQueryFactory::create(ParticipantsTable::class)
                ->insert()
                ->addParameter(ParticipantsTable::threadId, $thread->getId())
                ->addParameter(ParticipantsTable::userId, $userId)
                ->addParameter(ParticipantsTable::isArchived, 0)
                ->addParameter(ParticipantsTable::lastActivity, time());
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
        $queryFactory = SqlQueryFactory::create(ParticipantsTable::class)
            ->update()
            ->addParameter(ParticipantsTable::threadId, $threadId)
            ->addParameter(ParticipantsTable::userId, $userId)
            ->addParameter(ParticipantsTable::isArchived, true);

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
        $queryFactory = SqlQueryFactory::create(ParticipantsTable::class)
            ->update()
            ->addParameter(ParticipantsTable::threadId, $threadId)
            ->addParameter(ParticipantsTable::userId, $userId)
            ->addParameter(ParticipantsTable::lastActivity, time());

        $this->data->update(
            queryFactory: $queryFactory,
        );
    }
}