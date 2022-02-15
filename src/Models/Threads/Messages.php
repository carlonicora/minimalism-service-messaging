<?php
namespace CarloNicora\Minimalism\Services\Messaging\Models\Threads;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Security\Interfaces\SecurityInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\IO\MessageIO;
use CarloNicora\Minimalism\Services\Messaging\Messaging;
use CarloNicora\Minimalism\Services\Messaging\Models\Abstracts\AbstractMessagingModel;
use Exception;

class Messages extends AbstractMessagingModel
{
    /**
     * @param SecurityInterface $security
     * @param Messaging $messaging
     * @param PositionedEncryptedParameter $threadId
     * @param PositionedEncryptedParameter|null $fromMessage
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        SecurityInterface $security,
        Messaging $messaging,
        PositionedEncryptedParameter $threadId,
        ?PositionedEncryptedParameter $fromMessage = null
    ): HttpCode
    {
        $this->requireValidUser();
        $thread = $this->validateThread($threadId->getValue());

        $this->document = $messaging->getMessagesList(
            threadId: $thread->getId(),
            userId: $security->getUserId(),
            fromMessageId: $fromMessage?->getValue()
        );

        $messaging->markThreadAsRead(
            userId: $security->getUserId(),
            threadId: $threadId->getValue(),
        );

        $this->document->forceResourceList();

        return HttpCode::Ok;
    }

    /**
     * @param SecurityInterface $security
     * @param Messaging $messaging
     * @param PositionedEncryptedParameter $threadId
     * @param array $payload
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        SecurityInterface $security,
        Messaging $messaging,
        PositionedEncryptedParameter $threadId,
        array $payload
    ): HttpCode
    {
        $this->requireValidUser();
        $thread = $this->validateThread($threadId->getValue());

        if (empty($payload['content'])) {
            throw new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'Message content missing',
            );
        }

        $newMessage = $messaging->sendMessage(
            userIdSender: $security->getUserId(),
            threadId: $thread->getId(),
            content: $payload['content']
        );

        $this->document->addResource(resource: $newMessage);

        return HttpCode::Created;
    }

    /**
     * @param SecurityInterface $security
     * @param Messaging $messaging
     * @param PositionedEncryptedParameter $threadId
     * @param PositionedEncryptedParameter $messageId
     * @return HttpCode
     * @throws Exception
     */
    public function delete(
        SecurityInterface $security,
        Messaging $messaging,
        PositionedEncryptedParameter $threadId,
        PositionedEncryptedParameter $messageId
    ): HttpCode
    {
        $this->requireValidUser();

        /** @noinspection UnusedFunctionResultInspection */
        $this->validateThread($threadId->getValue());

        $message = $this->objectFactory->create(MessageIO::class)->readByMessageId(
            messageId: $messageId->getValue(),
        );

        $messaging->deleteMessage(
            userId: $security->getUserId(),
            messageId: $message->getId()
        );

        return HttpCode::NoContent;
    }
}