<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Messaging\Data\Builders\ThreadBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\ThreadsTable;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\ThreadsDataReader;

class ThreadsResourceReader extends AbstractLoader
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
                type: DataFunction::TYPE_TABLE,
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
        /** @see ThreadsDataReader::byUserId() */
        return $this->builder->build(
            resourceTransformerClass: ThreadBuilder::class,
            function: new DataFunction(
                type: DataFunction::TYPE_LOADER,
                className: ThreadsDataReader::class,
                functionName: 'byUserId',
                parameters: [$userId, $fromTime]
            )
        );
    }
}