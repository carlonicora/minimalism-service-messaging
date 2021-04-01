<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Messaging\Data\Builders\MessageBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\MessagesDataReader;

class MessagesResourceReader extends AbstractLoader
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
        /** @see MessagesDataReader::byThreadId() */
        return $this->builder->build(
            resourceTransformerClass: MessageBuilder::class,
            function: new DataFunction(
                type: DataFunction::TYPE_LOADER,
                className: MessagesDataReader::class,
                functionName: 'byThreadId',
                parameters: [$threadId, $userId, $fromMessageId]
            )
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
        /** @see MessagesDataReader::byMessageId() */
        return current(
            $this->builder->build(
                resourceTransformerClass: MessageBuilder::class,
                function: new DataFunction(
                    type: DataFunction::TYPE_LOADER,
                    className: MessagesDataReader::class,
                    functionName: 'byMessageId',
                    parameters: [$messageId]
                )
            )
        );
    }
}