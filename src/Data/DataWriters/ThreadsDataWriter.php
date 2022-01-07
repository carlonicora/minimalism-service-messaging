<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DataWriters;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\Enums\ParticipantStatus;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\ParticipantsTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\ThreadsTable;

class ThreadsDataWriter extends AbstractLoader
{
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