<?php
namespace CarloNicora\Minimalism\Services\Messaging\Factories\Resources;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Data\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Interfaces\Data\Objects\DataFunction;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Messaging\Builders\MessageBuilder;
use CarloNicora\Minimalism\Services\Messaging\IO\MessageIO;

class MessagesResourceFactory extends AbstractLoader
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
        /** @see MessageIO::byThreadId() */
        return $this->builder->build(
            resourceTransformerClass: MessageBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: MessageIO::class,
                functionName: 'byThreadId',
                parameters: [$threadId, $userId, $fromMessageId]
            ),
            relationshipLevel: 2
        );
    }

    /**
     * @param int $messageId
     * @return ResourceObject
     */
    public function byMessageId(
        int $messageId,
    ): ResourceObject
    {
        /** @see MessageIO::byMessageId() */
        return current(
            $this->builder->build(
                resourceTransformerClass: MessageBuilder::class,
                function: new DataFunction(
                    type: DataFunctionInterface::TYPE_LOADER,
                    className: MessageIO::class,
                    functionName: 'byMessageId',
                    parameters: [$messageId]
                ),
                relationshipLevel: 2
            )
        );
    }

    /**
     * @param int $userId
     * @param int $lastChecked
     * @return array
     */
    public function newByUserId(
        int $userId,
        int $lastChecked,
    ): array
    {
        /** @see MessageIO::newByUserId() */
        return $this->builder->build(
            resourceTransformerClass: MessageBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: MessageIO::class,
                functionName: 'newByUserId',
                parameters: [$userId, $lastChecked]
            ),
            relationshipLevel: 2
        );
    }
}