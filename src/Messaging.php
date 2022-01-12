<?php
namespace CarloNicora\Minimalism\Services\Messaging;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\MessagesDataReader;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\ParticipantsDataReader;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\ThreadsDataReader;
use CarloNicora\Minimalism\Services\Messaging\Data\DataWriters\MessagesDataWriter;
use CarloNicora\Minimalism\Services\Messaging\Data\DataWriters\ThreadsDataWriter;
use CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders\MessagesResourceReader;
use CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders\ThreadsResourceReader;
use Exception;
use RuntimeException;

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
                $this->objectFactory->create(ThreadsResourceReader::class)?->getDialogThread(
                    userId1: $userId1,
                    userId2: $userId2
                )
            );
        } catch (RecordNotFoundException) {
            $newThreadId = $this->objectFactory->create(ThreadsDataWriter::class)?->create(
                userIds: [$userId1, $userId2]
            );

            $response->addResource(
                $this->objectFactory->create(ThreadsResourceReader::class)?->byId($newThreadId)
            );
        }

        return $response;
    }

    /**
     * @param int $userId
     * @param int|null $fromTime
     * @return Document
     * @throws Exception
     */
    public function getThreadsList(
        int $userId,
        int $fromTime=null,
    ): Document
    {
        $response = new Document();

        $response->addResourceList(
            resourceList: $this->objectFactory->create(ThreadsResourceReader::class)?->byUserId(
                userId: $userId,
                fromTime: $fromTime
            ),
        );

        return $response;
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
            resourceList: $this->objectFactory->create(MessagesResourceReader::class)?->byThreadId(
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
            resource: $this->objectFactory->create(MessagesResourceReader::class)?->byMessageId(
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

        $threadId = $this->objectFactory->create(ThreadsDataWriter::class)?->create(
            userIds: $userIds
        );

        /** @noinspection UnusedFunctionResultInspection */
        $this->sendMessage(
            userIdSender: $userIdSender,
            threadId: $threadId,
            content: $content
        );

        return $this->objectFactory->create(ThreadsResourceReader::class)?->byId($threadId);
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
        $participants = $this->objectFactory->create(ParticipantsDataReader::class)?->byThreadId($threadId);
        $participantIds = array_column($participants, 'userId');
        if (!in_array($userIdSender, $participantIds, true)) {
            throw new RuntimeException('User is not a thread participant', 403);
        }

        $messageId = $this->objectFactory->create(MessagesDataWriter::class)?->create(
            userIdSender: $userIdSender,
            threadId: $threadId,
            content: $content
        );

        return $this->objectFactory->create(MessagesResourceReader::class)?->byMessageId($messageId);
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
        $message = $this->objectFactory->create(MessagesDataReader::class)?->byMessageId($messageId);
        if ($message['userId'] !== $userId) {
            throw new RuntimeException('The current user has no access to a message', 403);
        }

        $this->objectFactory->create(MessagesDataWriter::class)?->delete(
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
        $this->objectFactory->create(ThreadsDataWriter::class)?->archive(
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
        $this->objectFactory->create(ThreadsDataWriter::class)?->markAsRead(
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
        return $this->objectFactory->create(ThreadsDataReader::class)?->countByUserId(
            userId: $userId
        );
    }
}