<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Participants\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlFieldFactory;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Cache\MessagingCacheFactory;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\Databases\ParticipantsTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\DataObjects\Participant;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\DataObjects\Thread;
use JetBrains\PhpStorm\ArrayShape;

class ParticipantIO extends AbstractMessagingIO
{

    /**
     * @param int $threadId
     * @return Participant[]
     * @throws MinimalismException
     */
    public function byThreadId(
        int $threadId
    ): array
    {
        // We use it in Notifier
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: ParticipantsTable::class)
                ->addParameter(field: ParticipantsTable::threadId, value: $threadId),
            responseType: Thread::class,
            requireObjectsList: true
        );
    }

    /**
     * @param int $threadId
     * @return int[]
     * @throws MinimalismException
     */
    public function participantIdsByThreadId(
        int $threadId,
    ): array
    {
        $result = $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: ParticipantsTable::class)
                ->addParameter(field: ParticipantsTable::threadId, value: $threadId),
            cacheBuilder: MessagingCacheFactory::threadParticipants($threadId),
        );

        return array_column($result, SqlFieldFactory::create(field: ParticipantsTable::userId)->getName());
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
            queryFactory: SqlQueryFactory::create(tableClass: ParticipantsTable::class)
                ->update()
                ->addParameter(field: ParticipantsTable::threadId, value: $threadId)
                ->addParameter(field: ParticipantsTable::isArchived, value: 0)
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