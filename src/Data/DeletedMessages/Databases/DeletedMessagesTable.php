<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\DeletedMessages\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'deletedMessages', databaseIdentifier: 'Messaging')]
enum DeletedMessagesTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer,fieldOption: SqlFieldOption::AutoIncrement)]
    case deletedMessageId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case userId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case messageId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;
}