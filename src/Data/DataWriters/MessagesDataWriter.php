<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DataWriters;

use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingLoader;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\DeletedMessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\MessagesTable;

class MessagesDataWriter extends AbstractMessagingLoader
{
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