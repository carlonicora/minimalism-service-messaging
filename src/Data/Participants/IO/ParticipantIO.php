<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Participants\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Cache\MessagingCacheFactory;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\Databases\ParticipantsTable;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use JetBrains\PhpStorm\ArrayShape;

class ParticipantIO extends AbstractMessagingIO
{
    /**
     * @param int $threadId
     * @return array
     * @throws MinimalismException
     */
    public function byThreadId(
        int $threadId,
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(ParticipantsTable::class)
                ->selectAll()
                ->addParameter(ParticipantsTable::threadId, $threadId),
            cacheBuilder: MessagingCacheFactory::threadParticipants($threadId),
        );
    }

    /**
     * @param int $threadId
     * @return void
     * @throws MinimalismException
     */
    public function unarchiveThread(
        int $threadId,
    ): void
    {
        $this->data->update(
            queryFactory: SqlQueryFactory::create(ParticipantsTable::class)
                ->update()
                ->addParameter(ParticipantsTable::threadId, $threadId)
                ->addParameter(ParticipantsTable::isArchived, 0)
        );
    }

    //TODO REMOVE THIS THING BELOW!
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