<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DataWriters;

use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingLoader;

class ThreadsDataWriter extends AbstractMessagingLoader
{
    /**
     * @param int $threadId
     * @param int $userId
     */
    public function archive(
        int $threadId,
        int $userId
    ): void
    {

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

    }
}