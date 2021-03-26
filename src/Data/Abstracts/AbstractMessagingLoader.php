<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Abstracts;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Interfaces\CacheBuilderFactoryInterface;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\Factories\MessagingCacheFactory;
use CarloNicora\Minimalism\Services\Messaging\Messaging;

class AbstractMessagingLoader extends AbstractLoader
{
    /** @var CacheBuilderFactoryInterface|MessagingCacheFactory|null  */
    protected CacheBuilderFactoryInterface|MessagingCacheFactory|null $cacheFactory;

    /** @var ServiceInterface|Messaging|null  */
    protected ServiceInterface|Messaging|null $defaultService;
}