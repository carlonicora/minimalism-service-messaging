<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class ParticipantsTable extends AbstractMySqlTable
{
    /** @var string */
    protected string $tableName = 'participants';

    /** @var array  */
    protected array $fields = [
        'threadId'      => FieldInterface::INTEGER
                        +  FieldInterface::PRIMARY_KEY,
        'userId'        => FieldInterface::STRING
                        +  FieldInterface::PRIMARY_KEY,
        'isAchieved'    => FieldInterface::INTEGER,
        'lastActivity'  => FieldInterface::STRING
    ];

    /**
     * @param int $threadId
     * @return array
     * @throws Exception
     */
    public function readByThreadId(
        int $threadId
    ): array
    {
        $this->sql = 'SELECT userId FROM participants WHERE threadId=?;';
        $this->parameters = ['i', $threadId];

        return $this->functions->runRead();
    }
}