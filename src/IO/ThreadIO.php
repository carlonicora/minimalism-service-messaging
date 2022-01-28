<?php
namespace CarloNicora\Minimalism\Services\Messaging\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Messaging\Data\Thread;
use CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables\Enums\ParticipantStatus;
use CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables\ParticipantsTable;
use CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables\ThreadsTable;
use CarloNicora\Minimalism\Services\Messaging\Factories\MessagingCacheFactory;
use Exception;

class ThreadIO extends AbstractLoader
{
    /**
     * @param int $threadId
     * @return Thread
     * @throws Exception
     */
    public function readByThreadId(
        int $threadId,
    ): Thread
    {
        /** @see ThreadsTable::readById() */
        return $this->returnSingleObject(
            recordset: $this->data->read(
                tableInterfaceClassName: ThreadsTable::class,
                functionName: 'readById',
                parameters: [$threadId],
                cacheBuilder: MessagingCacheFactory::thread($threadId),
            ),
            objectType: Thread::class,
        );
    }

    /**
     * @param int $userId
     * @param int|null $fromTime
     * @return array
     */
    public function byUserId(
        int $userId,
        ?int $fromTime=null
    ): array
    {
        /** @see ThreadsTable::readByUserId() */
        return $this->data->read(
            tableInterfaceClassName: ThreadsTable::class,
            functionName: 'readByUserId',
            parameters: [$userId, $fromTime],
        );
    }

    public function countByUserId(
        int $userId
    ): int
    {
        /** @see ThreadsTable::readUnreadCount() */
        return $this->data->count(
            tableInterfaceClassName: ThreadsTable::class,
            functionName: 'readUnreadCount',
            parameters: [$userId]
        );
    }

    /**
     * @param int $messageId
     * @return array
     * @throws Exception
     */
    public function byMessageId(
        int $messageId
    ): array
    {
        /** @see ThreadsTable::byMessageId() */
        $result = $this->data->read(
            tableInterfaceClassName: ThreadsTable::class,
            functionName: 'byMessageId',
            parameters: ['messageId' => $messageId],
        );
        return $this->returnSingleValue($result);
    }

    /**
     * @param int $userId1
     * @param int $userId2
     * @return array
     * @throws Exception
     */
    public function getDialogThread(
        int $userId1,
        int $userId2
    ): array
    {
        /** @see ThreadsTable::loadDialogThread() */
        $result = $this->data->read(
            tableInterfaceClassName: ThreadsTable::class,
            functionName: 'loadDialogThread',
            parameters: [
                'userId1' => $userId1,
                'userId2' => $userId2
            ]
        );

        return $this->returnSingleValue($result);
    }

    /**
     * @param array $userIds
     * @return int
     */
    public function create(
        array $userIds
    ): int
    {
        $thread = $this->data->insert(
            tableInterfaceClassName: ThreadsTable::class,
            records: ['threadId' => null]
        );

        $participants = [];

        foreach ($userIds as $userId){
            $participants[] = [
                'threadId' => $thread['threadId'],
                'userId' => $userId,
                'isArchived' => ParticipantStatus::Active->value,
                'lastActivity' => date('Y-m-d H:i:s')
            ];
        }

        /** @noinspection UnusedFunctionResultInspection */
        $this->data->insert(
            tableInterfaceClassName: ParticipantsTable::class,
            records: $participants
        );

        return $thread['threadId'];
    }

    /**
     * @param int $threadId
     * @param int $userId
     */
    public function archive(
        int $threadId,
        int $userId
    ): void
    {
        /** @See ParticipantsTable::updateThreadArchived() */
        /** @noinspection UnusedFunctionResultInspection */
        $this->data->run(
            tableInterfaceClassName: ParticipantsTable::class,
            functionName: 'updateThreadArchived',
            parameters: [$threadId,$userId]
        );
    }

    /**
     * @param int $userId
     * @param int $threadId
     */
    public function markAsRead(
        int $userId,
        int $threadId,
    ): void
    {
        /** @See ParticipantsTable::updateThreadAsRead() */
        /** @noinspection UnusedFunctionResultInspection */
        $this->data->run(
            tableInterfaceClassName: ParticipantsTable::class,
            functionName: 'updateThreadAsRead',
            parameters: [$threadId,$userId]
        );
    }
}