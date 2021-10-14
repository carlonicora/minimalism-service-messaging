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
    protected static array $fields = [
        'messageId'     => FieldInterface::INTEGER
                        +  FieldInterface::PRIMARY_KEY
                        +  FieldInterface::AUTO_INCREMENT,
        'threadId'      => FieldInterface::INTEGER,
        'userId'        => FieldInterface::INTEGER,
        'content'       => FieldInterface::STRING,
        'createdAt'     => FieldInterface::STRING
                        +  FieldInterface::TIME_CREATE
    ];

    /**
     * @param int $threadId
     * @param int $userId
     * @param int|null $fromMessageId
     * @return array
     * @throws Exception
     */
    public function readByThreadId(
        int $threadId,
        int $userId,
        ?int $fromMessageId=null
    ): array
    {
        $this->sql = 'SELECT messages.messageId, messages.userId, messages.content, messages.createdAt, IF(createdAt>=participants.lastActivity, 1, 0) as unread'
            . ' FROM messages'
            . ' JOIN participants ON participants.threadId=? AND participants.userId=?'
            . ' WHERE messages.threadId=?'
            . ' AND messages.messageId NOT IN (SELECT messageId FROM deleted_messages WHERE userId=?)';
        $this->parameters = ['iiii', $threadId, $userId, $threadId, $userId];

        if ($fromMessageId !== null){
            $this->sql .= ' AND messages.messageId<?';
            $this->parameters[0] .= 'i';
            $this->parameters[] = $fromMessageId;
        }

        $this->sql .= ' ORDER BY messages.createdAt DESC'
            . ' LIMIT 0,25;';

        return $this->functions->runRead();
    }

    /**
     * @param int $messageId
     * @return array
     * @throws Exception
     */
    public function readByMessageId(
        int $messageId,
    ): array
    {
        $this->sql = 'SELECT messageId, userId, content, createdAt, 1 as unread'
            . ' FROM messages'
            . ' WHERE messages.messageId=?';
        $this->parameters = ['i', $messageId];

        return $this->functions->runRead();
    }
}