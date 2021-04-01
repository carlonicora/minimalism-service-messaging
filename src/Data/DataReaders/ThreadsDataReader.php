<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DataReaders;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\ThreadsTable;

class ThreadsDataReader extends AbstractLoader
{
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
}