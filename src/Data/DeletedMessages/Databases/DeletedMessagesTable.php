<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DeletedMessages\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'deleted_messages', databaseIdentifier: 'Messaging')]
enum DeletedMessagesTable
{
    #[SqlField(fieldType: FieldType::Integer,fieldOption: FieldOption::AutoIncrement)]
    case deletedMessageId;

    #[SqlField(fieldType: FieldType::Integer)]
    case userId;

    #[SqlField(fieldType: FieldType::Integer)]
    case messageId;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;
}