<?php
namespace CarloNicora\Minimalism\Services\Messaging\Tests\Unit\Data\DataReaders;

use CarloNicora\Minimalism\Interfaces\DataInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\MessagesDataReader;
use CarloNicora\Minimalism\Services\Messaging\Tests\Abstracts\AbstractUnitTest;

class MessagesDataReaderTest extends AbstractUnitTest
{
    /**
     *
     */
    public function testByThreadIdEmpty(): void
    {
        $dataInterface = $this->getMockBuilder(DataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dataInterface->method('read')
            ->willReturn([]);

        /** @var MessagesDataReader $readMessagesData */
        $readMessagesData = $this->getMockBuilder(MessagesDataReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods($this->getAllMethodNamesExcept(MessagesDataReader::class, ['byThreadId']))
            ->getMock();
        $this->setProperty($readMessagesData, 'data', $dataInterface);

        self::assertEquals([], $readMessagesData->byThreadId(threadId:1,userId: 1));
    }

    /**
     *
     */
    public function testByThreadId(): void
    {
        $response = [['threadId'=>1]];

        $dataInterface = $this->getMockBuilder(DataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dataInterface->method('read')
            ->willReturn($response);

        /** @var MessagesDataReader $readMessagesData */
        $readMessagesData = $this->getMockBuilder(MessagesDataReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods($this->getAllMethodNamesExcept(MessagesDataReader::class, ['byThreadId']))
            ->getMock();
        $this->setProperty($readMessagesData, 'data', $dataInterface);

        self::assertEquals($response, $readMessagesData->byThreadId(threadId:1,userId: 1));
    }
}