<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingLoader;

class MessagesResourceReader extends AbstractMessagingLoader
{
    /**
     * @param int $threadId
     * @param int $userId
     * @param int|null $fromMessageId
     * @return array
     */
    public function byThreadId(
        int $threadId,
        int $userId,
        ?int $fromMessageId=null,
    ): array
    {

    }

    /**
     * @param int $messageId
     * @return ResourceObject
     */
    public function byMessageId(
        int $messageId,
    ): ResourceObject
    {

    }
}