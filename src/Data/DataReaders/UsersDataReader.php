<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DataReaders;

use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingLoader;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\ParticipantsTable;

class UsersDataReader extends AbstractMessagingLoader
{
    /**
     * @param int $threadId
     * @return array
     */
    public function byThreadId(
        int $threadId
    ): array
    {
        /** @see ParticipantsTable::readByThreadId() */
        return $this->data->read(
            tableInterfaceClassName: ParticipantsTable::class,
            functionName: 'readByThreadId',
            parameters: [$threadId],
            cacheBuilder: $this->cacheFactory->threadParticipants($threadId)
        );
    }

    /**
     * @param int $userId
     * @return array
     */
    public function byUserId(
        int $userId
    ): array
    {
        return [
            'userId' => $userId
        ];
    }
}