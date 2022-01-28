<?php
namespace CarloNicora\Minimalism\Services\Messaging\Factories\Resources;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Data\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Interfaces\Data\Objects\DataFunction;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Messaging\Builders\ThreadBuilder;
use CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables\ThreadsTable;
use CarloNicora\Minimalism\Services\Messaging\IO\ThreadIO;
use Exception;

class ThreadsResourceFactory extends AbstractLoader
{
    /**
     * @param int $threadId
     * @return ResourceObject
     */
    public function byId(int $threadId): ResourceObject
    {
        /** @see ThreadsTable::byId() */
        return current($this->builder->build(
            resourceTransformerClass: ThreadBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_TABLE,
                className: ThreadsTable::class,
                functionName: 'byId',
                parameters: ['id' => $threadId]
            )
        ));
    }

    /**
     * @param int $userId
     * @param int|null $fromTime
     * @return array
     */
    public function byUserId(
        int $userId,
        int $fromTime=null,
    ): array
    {
        /** @see ThreadIO::byUserId() */
        return $this->builder->build(
            resourceTransformerClass: ThreadBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: ThreadIO::class,
                functionName: 'byUserId',
                parameters: [$userId, $fromTime]
            )
        );
    }

    /**
     * @param int $userId1
     * @param int $userId2
     * @return ResourceObject
     * @throws Exception
     */
    public function getDialogThread(
        int $userId1,
        int $userId2
    ): ResourceObject
    {
        /** @see ThreadIO::getDialogThread() */
        return current($this->builder->build(
            resourceTransformerClass: ThreadBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: ThreadIO::class,
                functionName: 'getDialogThread',
                parameters: [
                    'userId1' => $userId1,
                    'userId2' => $userId2
                ]
            )
        ));
    }
}