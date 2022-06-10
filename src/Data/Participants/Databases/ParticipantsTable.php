<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Participants\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'participants', databaseIdentifier: 'Messaging')]
enum ParticipantsTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer,fieldOption: SqlFieldOption::PrimaryKey)]
    case threadId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer,fieldOption: SqlFieldOption::PrimaryKey)]
    case userId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case isArchived;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeUpdate)]
    case lastActivity;
}