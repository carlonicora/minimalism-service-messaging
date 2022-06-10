<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Messages\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'messages', databaseIdentifier: 'Messaging')]
enum MessagesTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer,fieldOption: SqlFieldOption::AutoIncrement)]
    case messageId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case threadId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case userId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case content;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;
}