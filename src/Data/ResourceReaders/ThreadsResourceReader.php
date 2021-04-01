<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Messaging\Data\Builders\ThreadBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\ThreadsDataReader;

class ThreadsResourceReader extends AbstractLoader
{
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