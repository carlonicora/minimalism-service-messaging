<?php
namespace CarloNicora\Minimalism\Services\Messaging\Models\Threads;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Services\Messaging\Abstracts\AbstractMessagingModel;
use CarloNicora\Minimalism\Services\Messaging\Messaging;
use Exception;

class Archive extends AbstractMessagingModel
{
    /**
     * @param Messaging $messaging
     * @param PositionedEncryptedParameter $threadId
     * @return HttpCode
     * @throws Exception
     */
    public function put(
        Messaging $messaging,
        PositionedEncryptedParameter $threadId,
    ): HttpCode
    {
        $this->requireValidUser();
        $thread = $this->validateThread($threadId->getValue());

        $messaging->archiveThread(
            threadId: $thread->getId(),
            userId: $this->security->getUserId(),
        );

        return HttpCode::NoContent;
    }
}