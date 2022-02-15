<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Threads\Factories;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\IO\ParticipantIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\Builders\ThreadBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\DataObjects\Thread;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\IO\ThreadIO;
use CarloNicora\Minimalism\Services\Users\Data\Abstracts\AbstractUserResourceFactory;
use CarloNicora\Minimalism\Services\Users\Data\Users\DataObjects\User;
use Exception;

class ThreadsResourceFactory extends AbstractUserResourceFactory
{
    /**
     * @param int $threadId
     * @return ResourceObject
     * @throws Exception
     */
    public function byId(
        int $threadId,
    ): ResourceObject
    {
        $data = new Thread();
        $data->setId($threadId);

        foreach ($this->objectFactory->create(ParticipantIO::class)?->byThreadId($threadId) as $participant) {
            $user = new User();
            $user->setId($participant['userId']);

            $data->addUser($user);
        }

        return $this->builder->buildResource(
            builderClass: ThreadBuilder::class,
            data: $data,
        );
    }

    /**
     * @param int $userId
     * @param int|null $fromTime
     * @return ResourceObject[]
     * @throws Exception
     */
    public function byUserId(
        int $userId,
        int $fromTime=null,
    ): array
    {
        $data = $this->objectFactory->create(ThreadIO::class)->readByUserId($userId, $fromTime);

        return $this->builder->buildResources(
            builderClass: ThreadBuilder::class,
            data: $data,
        );
    }

    /**
     * @param int $userId1
     * @param int $userId2
     * @return ResourceObject
     * @throws Exception
     */
    public function getDialogThread(
        int $userId1,
        int $userId2
    ): ResourceObject
    {
        $data = $this->objectFactory->create(ThreadIO::class)->getDialogThread($userId1, $userId2);

        return $this->builder->buildResource(
            builderClass: ThreadBuilder::class,
            data: $data,
        );
    }
}