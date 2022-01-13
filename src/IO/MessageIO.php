<?php
namespace CarloNicora\Minimalism\Services\Messaging\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Messaging\Data\Message;
use CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables\DeletedMessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables\MessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables\ParticipantsTable;
use Exception;

class MessageIO extends AbstractLoader
{
    /**
     * @param int $messageId
     * @return Message
     * @throws Exception
     */
    public function readByMessageId(
        int $messageId,
    ): Message
    {
        /** @see MessagesTable::readById() */
        return $this->returnSingleObject(
            recordset: $this->data->read(
                tableInterfaceClassName: MessagesTable::class,
                functionName: 'readById',
                parameters: [$messageId],
            ),
            objectType: Message::class,
        );
    }

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

    /**
     * @param int $userIdSender
     * @param int $threadId
     * @param string $content
     * @return int
     */
    public function create(
        int $userIdSender,
        int $threadId,
        string $content,
    ): int
    {
        $message = [
            'threadId' => $threadId,
            'userId' => $userIdSender,
            'content' => $content
        ];

        $message = $this->data->insert(
            tableInterfaceClassName: MessagesTable::class,
            records: $message
        );

        /** @see ParticipantsTable::updateThreadUnarchived() */
        /** @noinspection UnusedFunctionResultInspection */
        $this->data->run(
            tableInterfaceClassName: ParticipantsTable::class,
            functionName: 'updateThreadUnarchived',
            parameters: [$threadId]
        );

        return $message['messageId'];
    }

    public function delete(
        int $userId,
        int $messageId,
    ): void
    {
        $deletedMessage = [
            'userId' => $userId,
            'messageId' => $messageId
        ];

        /** @noinspection UnusedFunctionResultInspection */
        $this->data->insert(
            tableInterfaceClassName: DeletedMessagesTable::class,
            records: $deletedMessage
        );
    }
}