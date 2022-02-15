<?php
namespace CarloNicora\Minimalism\Services\Messaging\Enums;

use CarloNicora\Minimalism\Services\Messaging\Data\Messages\Databases\MessagesTable;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\Databases\ThreadsTable;

enum MessagingDictionary: string
{
    case Message='message';
    case Thread='thread';
    case Author='author';

    /**
     * @return string
     */
    public function getEndpoint(
    ): string
    {
        return match ($this) {
            self::Message => 'messages',
            self::Thread => 'threads',
            self::Author => 'users',
        };
    }

    /**
     * @return string
     */
    public function getPlural(
    ): string
    {
        return $this->getEndpoint();
    }

    /**
     * @return string
     */
    public function getTableName(
    ): string
    {
        return match ($this) {
            self::Message => MessagesTable::class,
            self::Thread => ThreadsTable::class,
            default => '',
        };
    }
}