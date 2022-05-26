<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Messages\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'messages', databaseIdentifier: 'Messaging')]
enum MessagesTable
{
    #[SqlField(fieldType: FieldType::Integer,fieldOption: FieldOption::AutoIncrement)]
    case messageId;

    #[SqlField(fieldType: FieldType::Integer)]
    case threadId;

    #[SqlField(fieldType: FieldType::Integer)]
    case userId;

    #[SqlField]
    case content;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;
}