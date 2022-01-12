<?php
namespace CarloNicora\Minimalism\Services\Messaging\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables\ParticipantsTable;
use CarloNicora\Minimalism\Services\Messaging\Factories\MessagingCacheFactory;
use JetBrains\PhpStorm\ArrayShape;

class ParticipantIO extends AbstractLoader
{
    /**
     * @param int $threadId
     * @return array
     */
    public function byThreadId(int $threadId): array
    {
        $cacheFactory = new MessagingCacheFactory();

        /** @see ParticipantsTable::readByThreadId() */
        return $this->data->read(
            tableInterfaceClassName: ParticipantsTable::class,
            functionName: 'readByThreadId',
            parameters: [$threadId],
            cacheBuilder: $cacheFactory->threadParticipants($threadId)
        );
    }

    #[ArrayShape(['userId' => "int"])]
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