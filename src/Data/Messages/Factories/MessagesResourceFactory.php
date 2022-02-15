<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Messages\Factories;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractResourceFactory;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\Builders\MessageBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\IO\MessageIO;
use Exception;

class MessagesResourceFactory extends AbstractResourceFactory
{
    /**
     * @param int $threadId
     * @param int $userId
     * @param int|null $fromMessageId
     * @return array
     * @throws Exception
     */
    public function readByThreadId(
        int $threadId,
        int $userId,
        ?int $fromMessageId=null,
    ): array
    {
        return $this->builder->buildResources(
            builderClass: MessageBuilder::class,
            data: $this->objectFactory->create(MessageIO::class)->readByThreadId($threadId, $userId, $fromMessageId),
        );
    }

    /**
     * @param int $messageId
     * @return ResourceObject
     * @throws Exception
     */
    public function readByMessageId(
        int $messageId,
    ): ResourceObject
    {
        return $this->builder->buildResource(
            builderClass: MessageBuilder::class,
            data: $this->objectFactory->create(MessageIO::class)->readByMessageId($messageId),
        );
    }

    /**
     * @param int $userId
     * @param int $lastChecked
     * @return array
     * @throws Exception
     */
    public function newByUserId(
        int $userId,
        int $lastChecked,
    ): array
    {
        return $this->builder->buildResources(
            builderClass: MessageBuilder::class,
            data: $this->objectFactory->create(MessageIO::class)->readNewByUserId($userId, $lastChecked),
        );
    }
}