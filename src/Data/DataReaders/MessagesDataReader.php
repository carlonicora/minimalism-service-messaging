<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DataReaders;

use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingLoader;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\MessagesTable;

class MessagesDataReader extends AbstractMessagingLoader
{
    /**
     * @param int $threadId
     * @param int|null $fromMessageId
     * @return array
     */
    public function byThreadId(
        int $threadId,
        ?int $fromMessageId=null
    ): array
    {
        /** @see MessagesTable::readByThreadId() */
        return $this->data->read(
            tableInterfaceClassName: MessagesTable::class,
            functionName: 'readByThreadId',
            parameters: [$threadId, $fromMessageId],
        );
    }
}