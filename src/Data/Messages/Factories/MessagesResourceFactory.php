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
    public function byThreadId(
        int $threadId,
        int $userId,
        ?int $fromMessageId=null,
    ): array
    {
        /** @var MessageIO $messageIO */
        $messageIO = $this->objectFactory->create(className: MessageIO::class);
        return $this->builder->buildResources(
            builderClass: MessageBuilder::class,
            data: $messageIO->byThreadId($threadId, $userId, $fromMessageId),
        );
    }

    /**
     * @param int $messageId
     * @return ResourceObject
     * @throws Exception
     */
    public function byMessageId(
        int $messageId,
    ): ResourceObject
    {
        /** @var MessageIO $messageIO */
        $messageIO = $this->objectFactory->create(className: MessageIO::class);
        return $this->builder->buildResource(
            builderClass: MessageBuilder::class,
            data: $messageIO->byMessageId($messageId),
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
        /** @var MessageIO $messageIO */
        $messageIO = $this->objectFactory->create(className: MessageIO::class);
        return $this->builder->buildResources(
            builderClass: MessageBuilder::class,
            data: $messageIO->newByUserId($userId, $lastChecked),
        );
    }
}