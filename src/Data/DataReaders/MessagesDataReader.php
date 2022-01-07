<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DataReaders;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\MessagesTable;

class MessagesDataReader extends AbstractLoader
{
    /**
     * @param int $threadId
     * @param int $userId
     * @param int|null $fromMessageId
     * @return array
     */
    public function byThreadId(
        int $threadId,
        int $userId,
        ?int $fromMessageId=null
    ): array
    {
        /** @see MessagesTable::readByThreadId() */
        return $this->data->read(
            tableInterfaceClassName: MessagesTable::class,
            functionName: 'readByThreadId',
            parameters: [$threadId, $userId, $fromMessageId],
        );
    }

    /**
     * @param int $messageId
     * @return array
     * @throws RecordNotFoundException
     */
    public function byMessageId(
        int $messageId
    ): array
    {
        /** @see MessagesTable::readByMessageId() */
        $messages = $this->data->read(
            tableInterfaceClassName: MessagesTable::class,
            functionName: 'readByMessageId',
            parameters: [$messageId],
        );

        return $this->returnSingleValue($messages);
    }

    /**
     * @param int $userId
     * @param int $lastChecked
     * @return array
     */
    public function newByUserId(
        int $userId,
        int $lastChecked,
    ): array
    {
        /** @see MessagesTable::readNewMessagesForUserId() */
        return $this->data->read(
            tableInterfaceClassName: MessagesTable::class,
            functionName: 'readNewMessagesForUserId',
            parameters: [$userId, $lastChecked],
        );
    }
}