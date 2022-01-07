<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DataReaders;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\ParticipantsTable;

class ParticipantsDataReader extends AbstractLoader
{
    /**
     * @param int $threadId
     * @return array
     */
    public function byThreadId(int $threadId): array
    {
        /** @see ParticipantsTable::readByThreadId() */
        return $this->data->read(
            tableInterfaceClassName: ParticipantsTable::class,
            functionName: 'readByThreadId',
            parameters: ['threadId' => $threadId],
        );
    }
}