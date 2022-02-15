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
        protected ObjectFactory $objectFactory,
        protected Path $path,
        protected EncrypterInterface $encrypter,
        protected Users $users,
    )
    {
    }
}