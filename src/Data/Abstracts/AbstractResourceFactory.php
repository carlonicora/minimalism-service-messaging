<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Abstracts;

use CarloNicora\Minimalism\Factories\ObjectFactory;
use CarloNicora\Minimalism\Interfaces\SimpleObjectInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\Cache\MessagingCacheFactory;
use CarloNicora\Minimalism\Services\ResourceBuilder\ResourceBuilder;

class AbstractResourceFactory implements SimpleObjectInterface
{
    /**
     * @param ObjectFactory $objectFactory
     * @param ResourceBuilder $builder
     * @param MessagingCacheFactory $cacheBuilderFactory
     */
    public function __construct(
        protected ObjectFactory $objectFactory,
        protected ResourceBuilder $builder,
        protected MessagingCacheFactory $cacheBuilderFactory=new MessagingCacheFactory(),
    ){
    }
}