<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingLoader;

class ThreadsResourceReader extends AbstractMessagingLoader
{
    /**
     * @param int $userId
     * @return array
     */
    public function byUserId(
        int $userId
    ): array
    {

    }

    /**
     * @param int $threadId
     * @return ResourceObject
     */
    public function byThreadId(
        int $threadId
    ): ResourceObject
    {

    }
}