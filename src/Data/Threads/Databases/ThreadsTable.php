<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Threads\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'threads', databaseIdentifier: 'Messaging')]
enum ThreadsTable
{
    #[SqlField(fieldType: FieldType::Integer,fieldOption: FieldOption::AutoIncrement)]
    case threadId;
}