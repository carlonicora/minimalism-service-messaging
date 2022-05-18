<?php
namespace CarloNicora\Minimalism\Services\Messaging;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\DataObjects\Message;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\Factories\MessagesResourceFactory;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\IO\MessageIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\IO\ParticipantIO;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\Factories\ThreadsResourceFactory;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\IO\ThreadIO;
use Exception;

class Messaging extends AbstractService
{
    /**
     * @param int $userId1
     * @param int $userId2
     * @return Document
     * @throws Exception
     */
    public function getDialogThread(
        int $userId1,
        int $userId2
    ): Document
    {
        $response = new Document();

        try {
            $response->addResource(
                $this->objectFactory->create(className: ThreadsResourceFactory::class)?->getDialogThread(
                    userId1: $userId1,
                    userId2: $userId2
                )
            );
        } catch (Exception) {
            $newThreadId = $this->objectFactory->create(className: ThreadIO::class)?->insert(
                userIds: [$userId1, $userId2]
            );

            $response->addResource(
                $this->objectFactory->create(className: ThreadsResourceFactory::class)?->byId($newThreadId)
            );
        }

        return $response;
    }

    /**
     * @param int $userId
     * @param int|null $fromTime
     * @return ResourceObject[]
     * @throws Exception
     */
    public function getThreadsList(
        int $userId,
        int $fromTime=null,
    ): array
    {
        return $this->objectFactory->create(className: ThreadsResourceFactory::class)?->byUserId(
            userId: $userId,
            fromTime: $fromTime
        );
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @param int|null $fromMessageId
     * @return Document
     * @throws Exception
     */
    public function getMessagesList(
        int $threadId,
        int $userId,
        int $fromMessageId=null
    ): Document
    {
        $response = new Document();

        $response->addResourceList(
            resourceList: $this->objectFactory->create(className: MessagesResourceFactory::class)?->readByThreadId(
                threadId: $threadId,
                userId: $userId,
                fromMessageId: $fromMessageId
            ),
        );

        return $response;
    }

    /**
     * @param int $messageId
     * @return Document
     * @throws Exception
     */
    public function getMessage(
        int $messageId,
    ): Document
    {
        $response = new Document();

        $response->addResource(
            resource: $this->objectFactory->create(className: MessagesResourceFactory::class)?->readByMessageId(
                messageId: $messageId
            ),
        );

        return $response;
    }

    /**
     * @param int $userIdSender
     * @param array $userIds
     * @param string $content
     * @return ResourceObject
     * @throws Exception
     */
    public function createThread(
        int $userIdSender,
        array $userIds,
        string $content,
    ): ResourceObject
    {
        $userIds[] = $userIdSender;

        $threadId = $this->objectFactory->create(className: ThreadIO::class)?->insert(
            userIds: $userIds
        );

        /** @noinspection UnusedFunctionResultInspection */
        $this->sendMessage(
            userIdSender: $userIdSender,
            threadId: $threadId,
            content: $content
        );

        return $this->objectFactory->create(className: ThreadsResourceFactory::class)?->byId($threadId);
    }

    /**
     * @param int $userIdSender
     * @param int $threadId
     * @param string $content
     * @return ResourceObject
     * @throws Exception
     */
    public function sendMessage(
        int $userIdSender,
        int $threadId,
        string $content,
    ): ResourceObject
    {
        $participants = $this->objectFactory->create(ParticipantIO::class)?->byThreadId($threadId);
        $participantIds = array_column($participants, 'userId');
        if (!in_array($userIdSender, $participantIds, true)) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'User is not a thread participant');
        }

        $message = new Message();
        $message->setUserId($userIdSender);
        $message->setThreadId($threadId);
        $message->setContent($content);

        $messageId = $this->objectFactory->create(className: MessageIO::class)?->insert(
            message: $message,
        );

        return $this->objectFactory->create(className: MessagesResourceFactory::class)?->readByMessageId($messageId);
    }

    /**
     * @param int $userId
     * @param int $messageId
     * @throws Exception
     */
    public function deleteMessage(
        int $userId,
        int $messageId
    ): void
    {
        $message = $this->objectFactory->create(className: MessageIO::class)?->byMessageId($messageId);
        if ($message['userId'] !== $userId) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'The current user has no access to a message');
        }

        $this->objectFactory->create(className: MessageIO::class)?->deleteByUserIdMessageId(
            userId: $userId,
            messageId: $messageId
        );
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @throws Exception
     */
    public function archiveThread(
        int $threadId,
        int $userId,
    ): void
    {
        $this->objectFactory->create(className: ThreadIO::class)?->archive(
            threadId: $threadId,
            userId: $userId
        );
    }

    /**
     * @param int $userId
     * @param int $threadId
     * @throws Exception
     */
    public function markThreadAsRead(
        int $userId,
        int $threadId,
    ): void
    {
        $this->objectFactory->create(className: ThreadIO::class)?->markAsRead(
            userId: $userId,
            threadId: $threadId
        );
    }

    /**
     * @param int $userId
     * @return int
     * @throws Exception
     */
    public function countUnreadThreads(
        int $userId
    ): int
    {
        return $this->objectFactory->create(className: ThreadIO::class)?->countByUserId(
            userId: $userId
        );
    }
}