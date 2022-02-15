<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Abstracts;

use CarloNicora\Minimalism\Factories\ObjectFactory;
use CarloNicora\Minimalism\Interfaces\Cache\Interfaces\CacheInterface;
use CarloNicora\Minimalism\Interfaces\Security\Interfaces\SecurityInterface;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlInterface;
use CarloNicora\Minimalism\Services\Messaging\Messaging;

abstract class AbstractMessagingIO extends AbstractSqlIO
{
    /**
     * @param ObjectFactory $objectFactory
     * @param SqlInterface $data
     * @param CacheInterface|null $cache
     * @param Messaging|null $messaging
     * @param SecurityInterface|null $authorisation
     */
    public function __construct(
        ObjectFactory $objectFactory,
        SqlInterface $data,
        ?CacheInterface $cache=null,
        protected ?Messaging $messaging=null,
        protected ?SecurityInterface $authorisation=null,
    )
    {
        parent::__construct(
            objectFactory: $objectFactory,
            data: $data,
            cache: $cache,
        );
    }
}