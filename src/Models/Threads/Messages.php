<?php
namespace CarloNicora\Minimalism\Services\Messaging\Models\Threads;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Security\Interfaces\SecurityInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\IO\MessageIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\IO\ParticipantIO;
use CarloNicora\Minimalism\Services\Messaging\Messaging;
use CarloNicora\Minimalism\Services\Messaging\Models\Abstracts\AbstractMessagingModel;
use CarloNicora\Minimalism\Services\Path;
use CarloNicora\Minimalism\Services\Users\Data\Users\DataObjects\User;
use CarloNicora\Minimalism\Services\Users\Users;
use Exception;

class Messages extends AbstractMessagingModel
{
    /**
     * @param SecurityInterface $security
     * @param Messaging $messaging
     * @param Users $userService
     * @param PositionedEncryptedParameter $threadId
     * @param PositionedEncryptedParameter|null $fromMessage
     * @return HttpCode
     * @throws MinimalismException
     * @throws Exception
     */
    public function get(
        SecurityInterface             $security,
        Messaging                     $messaging,
        Users                         $userService,
        PositionedEncryptedParameter  $threadId,
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

        /** @var ParticipantIO $participantIO */
        $participantIO = $this->objectFactory->create(className: ParticipantIO::class);
        $participants = [];
        foreach ($participantIO->participantIdsByThreadId($threadId->getValue()) as $participantId) {
            $participant = new User();
            $participant->setId($participantId);

            $participants []= $participant;
        }

        $this->document->links->add(new Link(
            name: 'participants',
            href: $userService->getUserUrlByIds($participants),
        ));

        $messaging->markThreadAsRead(
            userId: $security->getUserId(),
            threadId: $threadId->getValue(),
        );

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

        $message = $this->objectFactory->create(MessageIO::class)->byMessageId(
            messageId: $messageId->getValue(),
        );

        $messaging->deleteMessage(
            userId: $security->getUserId(),
            messageId: $message->getId()
        );

        return HttpCode::NoContent;
    }
}