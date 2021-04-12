<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;

class DeletedMessagesTable extends AbstractMySqlTable
{
    /** @var string */
    protected string $tableName = 'deleted_messages';

    /** @var array  */
    protected array $fields = [
        'deletedMessageId'  => FieldInterface::INTEGER
                            +  FieldInterface::PRIMARY_KEY
                            +  FieldInterface::AUTO_INCREMENT,
        'userId'            => FieldInterface::INTEGER,
        'messageId'         => FieldInterface::INTEGER,
        'createdAt'         => FieldInterface::STRING
                            +  FieldInterface::TIME_CREATE
    ];
}