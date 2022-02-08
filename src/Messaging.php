<?php /** @noinspection PropertyInitializationFlawsInspection */

namespace CarloNicora\Minimalism\Services\Messaging;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Interfaces\DataLoaderInterface;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\MessagesDataReader;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\ParticipantsDataReader;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\ThreadsDataReader;
use CarloNicora\Minimalism\Services\Messaging\Data\DataWriters\MessagesDataWriter;
use CarloNicora\Minimalism\Services\Messaging\Data\DataWriters\ThreadsDataWriter;
use CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders\MessagesResourceReader;
use CarloNicora\Minimalism\Services\Messaging\Data\ResourceReaders\ThreadsResourceReader;
use CarloNicora\Minimalism\Services\Pools;
use Exception;
use RuntimeException;

class Messaging implements ServiceInterface
{
    /** @var ThreadsDataReader|DataLoaderInterface|null  */
    private ThreadsDataReader|DataLoaderInterface|null $readThreadsData=null;

    /** @var MessagesResourceReader|DataLoaderInterface|null  */
    private MessagesResourceReader|DataLoaderInterface|null $readMessagesResources=null;

    /** @var ThreadsResourceReader|DataLoaderInterface|null  */
    private ThreadsResourceReader|DataLoaderInterface|null $readThreadsResources=null;

    /** @var MessagesDataReader|DataLoaderInterface|null  */
    private MessagesDataReader|DataLoaderInterface|null $readMessageData=null;

    /** @var MessagesDataWriter|DataLoaderInterface|null  */
    private MessagesDataWriter|DataLoaderInterface|null $writeMessageData=null;

    /** @var ThreadsDataWriter|DataLoaderInterface|null  */
    private ThreadsDataWriter|DataLoaderInterface|null $writeThreadsData=null;

    /** @var ParticipantsDataReader|DataLoaderInterface|null */
    private ParticipantsDataReader|DataLoaderInterface|null $readParticipantsData=null;

    /**
     * Messaging constructor.
     * @param Pools $pools
     */
    public function __construct(
        private Pools $pools,
    )
    {
    }

    /**
     * @return MessagesResourceReader
     * @throws Exception
     */
    private function getReadMessagesResources(): MessagesResourceReader
    {
        if ($this->readMessagesResources === null){
            $this->readMessagesResources = $this->pools->get(MessagesResourceReader::class);
        }

        return $this->readMessagesResources;
    }

    /**
     * @return ThreadsResourceReader
     * @throws Exception
     */
    private function getReadThreadsResources(): ThreadsResourceReader
    {
        if ($this->readThreadsResources === null){
            $this->readThreadsResources = $this->pools->get(ThreadsResourceReader::class);
        }

        return $this->readThreadsResources;
    }

    /**
     * @return ThreadsDataReader
     * @throws Exception
     */
    private function getReadThreadsData(): ThreadsDataReader
    {
        if ($this->readThreadsData === null){
            $this->readThreadsData = $this->pools->get(ThreadsDataReader::class);
        }

        return $this->readThreadsData;
    }

    /**
     * @return ThreadsDataWriter
     * @throws Exception
     */
    private function getWriteThreadsData(): ThreadsDataWriter
    {
        if ($this->writeThreadsData === null){
            $this->writeThreadsData = $this->pools->get(ThreadsDataWriter::class);
        }

        return $this->writeThreadsData;
    }

    /**
     * @return MessagesDataWriter
     * @throws Exception
     */
    private function getWriteMessagesData(): MessagesDataWriter
    {
        if ($this->writeMessageData === null){
            $this->writeMessageData = $this->pools->get(MessagesDataWriter::class);
        }

        return $this->writeMessageData;
    }

    /**
     * @return MessagesDataReader
     * @throws Exception
     */
    private function getReadMessagesData(): MessagesDataReader
    {
        if ($this->readMessageData === null){
            $this->readMessageData = $this->pools->get(MessagesDataReader::class);
        }

        return $this->readMessageData;
    }

    /**
     * @return ParticipantsDataReader
     * @throws Exception
     */
    private function getReadParticipantsData(): ParticipantsDataReader
    {
        if ($this->readParticipantsData === null) {
            $this->readParticipantsData = $this->pools->get(ParticipantsDataReader::class);
        }

        return $this->readParticipantsData;
    }

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
                $this->getReadThreadsResources()->getDialogThread(
                    userId1: $userId1,
                    userId2: $userId2
                )
            );
        } catch (RecordNotFoundException) {
            $newThreadId = $this->getWriteThreadsData()->create(
                userIds: [$userId1, $userId2]
            );

            $response->addResource(
                $this->getReadThreadsResources()->byId($newThreadId)
            );
        }

        return $response;
    }

    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     * @return Document
     * @throws Exception
     */
    public function getThreadsList(
        int $userId,
        int $offset,
        int $limit
    ): Document
    {
        $response = new Document();

        $response->addResourceList(
            resourceList: $this->getReadThreadsResources()->byUserId(
                userId: $userId,
                offset: $offset,
                limit: $limit
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
            resourceList: $this->getReadMessagesResources()->byThreadId(
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
     * @throws Exception
     */
    public function getMessage(
        int $messageId,
    ): Document
    {
        $response = new Document();

        $response->addResource(
            resource: $this->getReadMessagesResources()->byMessageId(
            messageId: $messageId
        )
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

        $threadId = $this->getWriteThreadsData()->create(
            userIds: $userIds
        );

        $this->sendMessage(
            userIdSender: $userIdSender,
            threadId: $threadId,
            content: $content
        );

        return $this->getReadThreadsResources()->byId($threadId);
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
        $participants = $this->getReadParticipantsData()->byThreadId($threadId);
        $participantIds = array_column($participants, 'userId');
        if (false === in_array($userIdSender, $participantIds, true)) {
            throw new RuntimeException('User is not a thread participant', 403);
        }

        $messageId = $this->getWriteMessagesData()->create(
            userIdSender: $userIdSender,
            threadId: $threadId,
            content: $content
        );

        return $this->getReadMessagesResources()->byMessageId($messageId);
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
        $message = $this->getReadMessagesData()->byMessageId($messageId);
        if ($message['userId'] !== $userId) {
            throw new RuntimeException('The current user has no access to a message', 403);
        }

        $this->getWriteMessagesData()->delete(
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
        $this->getWriteThreadsData()->archive(
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
        $this->getWriteThreadsData()->markAsRead(
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
        return $this->getReadThreadsData()->countByUserId(
            userId: $userId
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
        $this->readThreadsData=null;
        $this->readMessagesResources=null;
        $this->readThreadsResources=null;
        $this->readMessageData=null;
        $this->writeMessageData=null;
        $this->writeThreadsData=null;
        $this->readParticipantsData=null;
    }
}