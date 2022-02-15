<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Cache;

use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\Cacher\Factories\CacheBuilderFactory;

class MessagingCacheFactory extends CacheBuilderFactory
{
    /**
     * @param int $threadId
     * @return CacheBuilder
     */
    public static function thread(
        int $threadId,
    ): CacheBuilder
    {
        return self::create(
            cacheName: 'threadId',
            identifier: $threadId,
        );
    }

    /**
     * @param int $threadId
     * @return CacheBuilder
     */
    public static function threadParticipants(
        int $threadId
    ): CacheBuilder
    {
        return self::createList(
            listName: 'userId',
            cacheName: 'threadId',
            identifier: $threadId,
            saveGranular: false
        );
    }
}