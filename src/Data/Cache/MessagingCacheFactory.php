<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Cache;

use CarloNicora\Minimalism\Interfaces\Cache\Abstracts\AbstractCacheBuilderFactory;
use CarloNicora\Minimalism\Interfaces\Cache\Interfaces\CacheBuilderInterface;

class MessagingCacheFactory extends AbstractCacheBuilderFactory
{
    /**
     * @param int $threadId
     * @return CacheBuilderInterface
     */
    public static function thread(
        int $threadId,
    ): CacheBuilderInterface
    {
        return self::create(
            cacheName: 'threadId',
            identifier: $threadId,
        );
    }

    /**
     * @param int $threadId
     * @return CacheBuilderInterface
     */
    public static function threadParticipants(
        int $threadId
    ): CacheBuilderInterface
    {
        return self::createList(
            listName: 'userId',
            cacheName: 'threadId',
            identifier: $threadId,
            saveGranular: false
        );
    }
}