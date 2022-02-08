<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DataReaders;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\ThreadsTable;

class ThreadsDataReader extends AbstractLoader
{
    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function byUserId(
        int $userId,
        int $offset,
        int $limit
    ): array
    {
        /** @see ThreadsTable::readByUserId() */
        return $this->data->read(
            tableInterfaceClassName: ThreadsTable::class,
            functionName: 'readByUserId',
            parameters: [
                'userId' => $userId,
                'offset' => $offset,
                'limit' => $limit
            ],
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
     * @throws RecordNotFoundException
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
     * @throws RecordNotFoundException
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
}