<?php
namespace CarloNicora\Minimalism\Services\Messaging\Models\Abstracts;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Factories\MinimalismFactories;
use CarloNicora\Minimalism\Interfaces\Security\Interfaces\SecurityInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\IO\ParticipantIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\DataObjects\Thread;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\IO\ThreadIO;
use Exception;

abstract class AbstractMessagingModel extends AbstractModel
{
    /** @var SecurityInterface  */
    protected SecurityInterface $security;

    /**
     * @param MinimalismFactories $minimalismFactories
     * @param string|null $function
     */
    public function __construct(
        MinimalismFactories $minimalismFactories,
        ?string $function = null,
    )
    {
        parent::__construct($minimalismFactories, $function);

        $this->security = $minimalismFactories->getServiceFactory()->create(SecurityInterface::class);
    }

    /**
     * @param int $threadId
     * @return Thread
     * @throws Exception
     */
    protected function validateThread(
        int $threadId,
    ): Thread
    {
        $response = $this->objectFactory->create(ThreadIO::class)->readByThreadId(
            threadId: $threadId,
        );

        $this->validateParticipantInThread($threadId);

        return $response;
    }

    /**
     * @return void
     * @throws MinimalismException
     */
    protected function requireValidUser(
    ): void
    {
        if (!$this->security->isUser()){
            throw new MinimalismException(
                status: HttpCode::Forbidden,
                message: 'Access not allowed to guests',
            );
        }
    }

    /**
     * @param int $threadId
     * @return void
     * @throws Exception
     */
    private function validateParticipantInThread(
        int $threadId,
    ): void
    {
        $participants = $this->objectFactory->create(ParticipantIO::class)->byThreadId($threadId);

        foreach ($participants ?? [] as $participant){
            if ($this->security->getUserId() === $participant['userId']){
                return;
            }
        }

        throw new MinimalismException(
            status: HttpCode::Forbidden,
            message: 'User is not a participant in the requested thread',
        );
    }
}