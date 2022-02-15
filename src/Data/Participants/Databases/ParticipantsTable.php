<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Participants\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'participants', databaseIdentifier: 'Messaging')]
enum ParticipantsTable
{
    #[SqlField(fieldType: FieldType::Integer,fieldOption: FieldOption::PrimaryKey)]
    case threadId;

    #[SqlField(fieldType: FieldType::Integer,fieldOption: FieldOption::PrimaryKey)]
    case userId;

    #[SqlField(fieldType: FieldType::Integer)]
    case isArchived;

    #[SqlField(fieldOption: FieldOption::TimeUpdate)]
    case lastActivity;
}