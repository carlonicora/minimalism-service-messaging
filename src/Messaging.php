<?php
namespace CarloNicora\Minimalism\Services\Messaging;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\DataWriters\MessagesDataWriter;
use CarloNicora\Minimalism\Services\Messaging\Data\DataWriters\ThreadsDataWriter;
use CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders\MessagesResourceReader;
use CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders\ThreadsResourceReader;

class Messaging implements ServiceInterface
{
    public function __construct(
        private MessagesResourceReader $readMessagesResources,
        private ThreadsResourceReader $readThreadsResources,
        private MessagesDataWriter $writeMessageData,
        private ThreadsDataWriter $writeThreadsData,
    )
    {
    }

    /**
     * @param int $userId
     * @return Document
     */
    public function getThreadsList(
        int $userId,
    ): Document
    {
        $response = new Document();

        $response->addResourceList(
            resourceList: $this->readThreadsResources->byUserId(
                userId: $userId
            )
        );

        return $response;
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @param int|null $fromMessageId
     * @return Document
     */
    public function getMessagesList(
        int $threadId,
        int $userId,
        int $fromMessageId=null
    ): Document
    {
        $response = new Document();

        $response->addResourceList(
            resourceList: $this->readMessagesResources->byThreadId(
                threadId: $threadId,
                userId: $userId,
                fromMessageId: $fromMessageId
            )
        );

        return $response;
    }

    /**
     * @param int $messageId
     * @return Document
     */
    public function getMessage(
        int $messageId,
    ): Document
    {
        $response = new Document();

        $response->addResource(
            resource: $this->readMessagesResources->byMessageId(
                messageId: $messageId
            )
        );

        return $response;
    }

    /**
     * @param int $userIdSender
     * @param int $threadId
     * @param string $content
     * @return int
     */
    public function sendMessage(
        int $userIdSender,
        int $threadId,
        string $content,
    ): int
    {
        return $this->writeMessageData->create(
            userIdSender: $userIdSender,
            threadId: $threadId,
            content: $content
        );
    }

    /**
     * @param int $userId
     * @param int $messageId
     */
    public function deleteMessage(
        int $userId,
        int $messageId
    ): void
    {
        $this->writeMessageData->delete(
            userId: $userId,
            messageId: $messageId
        );
    }

    /**
     * @param int $threadId
     * @param int $userId
     */
    public function archiveThread(
        int $threadId,
        int $userId,
    ): void
    {
        $this->writeThreadsData->archive(
            threadId: $threadId,
            userId: $userId
        );
    }

    /**
     * @param int $userId
     * @param int $threadId
     */
    public function markThreadAsRead(
        int $userId,
        int $threadId,
    ): void
    {
        $this->writeThreadsData->markAsRead(
            userId: $userId,
            threadId: $threadId
        );
    }

    /**
     *
     */
    public function initialise(): void
    {
    }

    /**
     *
     */
    public function destroy(): void
    {
    }
}