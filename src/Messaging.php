<?php /** @noinspection PropertyInitializationFlawsInspection */

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

        /** @var ThreadsResourceReader $reader */
        $reader = $this->objectFactory->create(ThreadsResourceReader::class);

        /** @var ThreadsDataWriter $writer */
        $writer = $this->objectFactory->create(ThreadsDataWriter::class);

        try {
            $response->addResource(
                $reader->getDialogThread(
                    userId1: $userId1,
                    userId2: $userId2
                )
            );
        } catch (RecordNotFoundException) {
            $newThreadId = $writer->create(
                userIds: [$userId1, $userId2]
            );

            $response->addResource(
                $reader->byId($newThreadId)
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

        /** @var ThreadsResourceReader $reader */
        $reader = $this->objectFactory->create(ThreadsResourceReader::class);

        $response->addResourceList(
            resourceList: $reader->byUserId(
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

        /** @var MessagesResourceReader $reader */
        $reader = $this->objectFactory->create(MessagesResourceReader::class);

        $response->addResourceList(
            resourceList: $reader->byThreadId(
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

        /** @var MessagesResourceReader $reader */
        $reader = $this->objectFactory->create(MessagesResourceReader::class);

        $response->addResource(
            resource: $reader->byMessageId(
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

        /** @var ThreadsDataWriter $writer */
        $writer = $this->objectFactory->create(ThreadsDataWriter::class);

        $threadId = $writer->create(
            userIds: $userIds
        );

        /** @noinspection UnusedFunctionResultInspection */
        $this->sendMessage(
            userIdSender: $userIdSender,
            threadId: $threadId,
            content: $content
        );

        /** @var ThreadsResourceReader $reader */
        $reader = $this->objectFactory->create(ThreadsResourceReader::class);

        return $reader->byId($threadId);
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
        /** @var ParticipantsDataReader $participantReader */
        $participantReader = $this->objectFactory->create(ParticipantsDataReader::class);

        $participants = $participantReader->byThreadId($threadId);
        $participantIds = array_column($participants, 'userId');
        if (!in_array($userIdSender, $participantIds, true)) {
            throw new RuntimeException('User is not a thread participant', 403);
        }

        /** @var MessagesDataWriter $writer */
        $writer = $this->objectFactory->create(MessagesDataWriter::class);

        $messageId = $writer->create(
            userIdSender: $userIdSender,
            threadId: $threadId,
            content: $content
        );

        /** @var MessagesResourceReader $reader */
        $reader = $this->objectFactory->create(MessagesResourceReader::class);

        return $reader->byMessageId($messageId);
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
        /** @var MessagesDataReader $reader */
        $reader = $this->objectFactory->create(MessagesDataReader::class);

        $message = $reader->byMessageId($messageId);
        if ($message['userId'] !== $userId) {
            throw new RuntimeException('The current user has no access to a message', 403);
        }

        /** @var MessagesDataWriter $writer */
        $writer = $this->objectFactory->create(MessagesDataWriter::class);

        $writer->delete(
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
        /** @var ThreadsDataWriter $writer */
        $writer = $this->objectFactory->create(ThreadsDataWriter::class);

        $writer->archive(
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
        /** @var ThreadsDataWriter $writer */
        $writer = $this->objectFactory->create(ThreadsDataWriter::class);

        $writer->markAsRead(
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
        /** @var ThreadsDataReader $reader */
        $reader = $this->objectFactory->create(ThreadsDataReader::class);

        return $reader->countByUserId(
            userId: $userId
        );
    }
}