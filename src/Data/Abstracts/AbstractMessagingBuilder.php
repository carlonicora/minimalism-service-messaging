<?php

namespace CarloNicora\Minimalism\Services\Messaging\Data\Abstracts;

use CarloNicora\Minimalism\Factories\ObjectFactory;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Path;
use CarloNicora\Minimalism\Services\ResourceBuilder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\Users\Users;

abstract class AbstractMessagingBuilder extends AbstractResourceBuilder
{
    /**
     * @param ObjectFactory $objectFactory
     * @param Path $path
     * @param EncrypterInterface $encrypter
     * @param Users $users
     */
    public function __construct(
        protected readonly ObjectFactory      $objectFactory,
        protected readonly Path               $path,
        protected readonly EncrypterInterface $encrypter,
        protected readonly Users              $users,
    )
    {
    }
}