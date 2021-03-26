<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class MessagesTable extends AbstractMySqlTable
{
    /** @var string */
    protected string $tableName = 'messages';

    /** @var array  */
    protected array $fields = [
        'messageId'     => FieldInterface::INTEGER
                        +  FieldInterface::PRIMARY_KEY
                        +  FieldInterface::AUTO_INCREMENT,
        'threadId'      => FieldInterface::INTEGER,
        'userId'        => FieldInterface::INTEGER,
        'content'       => FieldInterface::STRING,
        'creationTime'  => FieldInterface::INTEGER
                        +  FieldInterface::TIME_CREATE
    ];

    /**
     * @param int $threadId
     * @param int|null $fromMessageId
     * @return array
     * @throws Exception
     */
    public function readByThreadId(
        int $threadId,
        ?int $fromMessageId=null
    ): array
    {
        $this->sql = 'SELECT messageId, userId, content, creationTime'
            . ' FROM messages'
            . ' WHERE threadId=?';
        $this->parameters = ['i', $threadId];

        if ($fromMessageId !== null){
            $this->sql .= ' AND messageId<?';
            $this->parameters[0] .= 'i';
            $this->parameters[] = $fromMessageId;
        }

        $this->sql .= ' ORDER BY creationTime DESC;';

        return $this->functions->runRead();
    }
}