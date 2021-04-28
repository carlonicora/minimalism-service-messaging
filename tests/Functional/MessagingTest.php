<?php
namespace CarloNicora\Minimalism\Services\Messaging\Tests\Functional;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Services\Messaging\Messaging;
use CarloNicora\Minimalism\Services\Messaging\Tests\Abstracts\AbstractFunctionalTest;
use Exception;

class MessagingTest extends AbstractFunctionalTest
{
    /** @var ServiceInterface|Messaging|null  */
    private ServiceInterface|Messaging|null $messaging;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->messaging = $this->getService(Messaging::class);
    }

    /**
     * @return ResourceObject
     * @throws Exception
     */
    public function testGetThreadList(): ResourceObject
    {
        $threads = $this->messaging->getThreadsList(
            userId: 1
        );

        self::assertCount(2,$threads->resources);

        return $threads->resources[0];
    }

    /**
     * @param ResourceObject $thread
     * @return ResourceObject
     * @depends testGetThreadList
     */
    public function testGetCorrectResource(ResourceObject $thread): ResourceObject
    {
        self::assertEquals('thread', $thread->type);

        return $thread;
    }

    /**
     * @param ResourceObject $thread
     * @depends testGetThreadList
     */
    public function testGetCorrectResourceAttributes(ResourceObject $thread): void
    {
        self::assertTrue($thread->attributes->has('lastMessageTime'));
        self::assertTrue($thread->attributes->has('lastMessage'));
        self::assertTrue($thread->attributes->has('unreadMessages'));
    }

    /**
     * @param ResourceObject $thread
     * @depends testGetThreadList
     */
    public function testGetCorrectUsers(ResourceObject $thread): void
    {
        foreach ($thread->relationships['members']->resourceLinkage->resources as $user){
            self::assertEquals('user', $user->type);
            self::assertTrue($user->links->has('self'));
        }
    }

    /**
     * @throws Exception
     */
    public function testGetThreadShortList(): void
    {
        $threads = $this->messaging->getThreadsList(
            userId: 1,
            fromTime: strtotime('2020-02-20 00:00:00')
        );

        self::assertCount(1,$threads->resources);
    }

    /**
     * @return ResourceObject
     * @throws Exception
     */
    public function testGetMessagesList(): ResourceObject
    {
        $messages = $this->messaging->getMessagesList(
            threadId: 1,
            userId: 1,
        );

        self::assertCount(25, $messages->resources);

        return $messages->resources[0];
    }

    /**
     * @param ResourceObject $message
     * @return ResourceObject
     * @depends testGetMessagesList
     */
    public function testGetCorrectMessageResource(ResourceObject $message): ResourceObject
    {
        self::assertEquals('message', $message->type);

        return $message;
    }

    /**
     * @param ResourceObject $message
     * @depends testGetCorrectMessageResource
     */
    public function testGetCorrectMessageResourceAttributes(ResourceObject $message): void
    {
        self::assertTrue($message->attributes->has('createdAt'));
        self::assertTrue($message->attributes->has('content'));
    }

    /**
     * @throws Exception
     */
    public function testGetMessagesListComplete(): void
    {
        $messages = $this->messaging->getMessagesList(
            threadId: 1,
            userId: 2867,
        );

        self::assertCount(25, $messages->resources);
    }

    /**
     * @throws Exception
     */
    public function testGetMessagesListCompleteFrom2(): void
    {
        $messages = $this->messaging->getMessagesList(
            threadId: 1,
            userId: 2867,
            fromMessageId: 2
        );

        self::assertCount(1, $messages->resources);
    }

    /**
     * @throws Exception
     */
    public function testGetMessage(): void
    {
        $messages = $this->messaging->getMessage(
            messageId: 1,
        );

        self::assertCount(1, $messages->resources);
        self::assertEquals('message', $messages->resources[0]->type);
    }

    /**
     * @return int
     * @throws Exception
     */
    public function testCreateThread(): int
    {
        $randomUser = time();
        $thread = $this->messaging->createThread(
            userIdSender: 3,
            userIds: [$randomUser],
            content: 'First Content of the thread',
        );

        self::assertNotNull($thread);

        return $this->getEncrypter()->decryptId($thread->id);
    }

    /**
     * @param int $threadId
     * @depends testCreateThread
     * @return int
     * @throws Exception
     */
    public function testCreateMessage(int $threadId): int
    {
        $message = $this->messaging->sendMessage(
            userIdSender: 3,
            threadId: $threadId,
            content: 'Second Content of the thread'
        );

        self::assertTrue(true);

        return $this->getEncrypter()->decryptId($message->id);
    }

    /**
     * @param int $messageId
     * @param int $threadId
     * @throws Exception
     * @depends testCreateMessage
     * @depends testCreateThread
     */
    public function testDeleteMessate(int $messageId, int $threadId): void
    {
        $this->messaging->deleteMessage(
            userId: 3,
            messageId: $messageId,
        );

        $thread = $this->messaging->getMessagesList(
            threadId: $threadId,
            userId: 3
        );

        self::assertCount(1, $thread->resources);
    }

    /**
     * @throws Exception
     */
    public function testCreateThreadToBeArchived(): int
    {
        $randomUser = time();
        $thread = $this->messaging->createThread(
            userIdSender: 4,
            userIds: [$randomUser],
            content: 'First Content of the thread',
        );

        self::assertNotNull($thread);

        return $this->getEncrypter()->decryptId($thread->id);
    }

    /**
     * @param int $threadId
     * @throws Exception
     * @depends testCreateThreadToBeArchived
     */
    public function testArchiveThread(int $threadId): void
    {
        $this->messaging->archiveThread(
            threadId: $threadId,
            userId: 4
        );

        $threads = $this->messaging->getThreadsList(
            userId: 4,
        );

        self::assertEmpty($threads->resources);
    }

    /**
     * @throws Exception
     */
    public function testgetUnreadThreadsCount(): void
    {
        $unreadThreads = $this->messaging->countUnreadThreads(
            userId: 1,
        );

        self::assertEquals(1, $unreadThreads);
    }
}