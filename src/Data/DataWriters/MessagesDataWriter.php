<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DataWriters;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\DeletedMessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\MessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\ParticipantsTable;

class MessagesDataWriter extends AbstractLoader
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