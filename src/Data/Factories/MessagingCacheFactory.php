<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Factories;

use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\Cacher\Factories\CacheBuilderFactory;

class MessagingCacheFactory extends CacheBuilderFactory
{
    /**
     * @param int $threadId
     * @return CacheBuilder
     */
    public function threadParticipants(
        int $threadId
    ): CacheBuilder
    {
        return $this->createList(
            listName: 'userId',
            cacheName: 'threadId',
            identifier: $threadId,
            saveGranular: false
        );
    }
}