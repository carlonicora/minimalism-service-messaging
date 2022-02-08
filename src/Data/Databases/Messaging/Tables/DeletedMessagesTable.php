<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;

class DeletedMessagesTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'deleted_messages';

    /** @var array  */
    protected static array $fields = [
        'deletedMessageId'  => FieldInterface::INTEGER
                            +  FieldInterface::PRIMARY_KEY
                            +  FieldInterface::AUTO_INCREMENT,
        'userId'            => FieldInterface::INTEGER,
        'messageId'         => FieldInterface::INTEGER,
        'createdAt'         => FieldInterface::STRING
                            +  FieldInterface::TIME_CREATE
    ];
}