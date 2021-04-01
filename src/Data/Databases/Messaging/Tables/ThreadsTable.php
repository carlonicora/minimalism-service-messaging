<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;
use RuntimeException;

class ThreadsTable extends AbstractMySqlTable
{
    /** @var string */
    protected string $tableName = 'threads';

    /** @var array  */
    protected array $fields = [
        'threadId'  => FieldInterface::INTEGER
                    +  FieldInterface::PRIMARY_KEY
                    +  FieldInterface::AUTO_INCREMENT,
    ];

    /**
     * @param int $userId
     * @param int|null $fromTime
     * @return array
     * @throws Exception
     */
    public function readByUserId(
        int $userId,
        int $fromTime=null,
    ): array
    {
        $this->sql = 'SELECT threads.threadId, messages.creationTime, messages.content, count(msgs.messageId) as unread'
            . ' FROM threads'
            . ' JOIN messages ON messages.messageId=(SELECT messages.messageId FROM messages WHERE messages.threadId=threads.threadId ORDER BY messages.creationTime DESC LIMIT 1)'
            . ' JOIN participants ON threads.threadId=participants.threadId'
            . ' LEFT JOIN messages msgs ON msgs.threadId=threads.threadId AND msgs.userId=? AND msgs.creationTime>=participants.lastActivity'
            . ' WHERE participants.userId=? AND participants.isArchived=?';

        $this->parameters = ['iii', $userId, $userId, 0];

        if ($fromTime !== null){
            $this->sql .= ' AND messages.creationTime<?';
            $this->parameters[0] .= 's';
            $this->parameters[] = date('Y-m-d H:i:s', $fromTime);
        }

        $this->sql .= ' GROUP BY threads.threadId, messages.creationTime, messages.content'
            . ' ORDER BY messages.creationTime DESC;';

        return $this->functions->runRead();
    }

    /**
     * @param int $userId
     * @return int
     * @throws Exception
     */
    public function readUnreadCount(
        int $userId
    ): int
    {
        $this->sql = 'SELECT count(threads.threadId) as counter'
            . ' FROM threads'
            . ' JOIN participants ON threads.threadId=participants.threadId AND participants.userId=?'
            . ' JOIN messages ON messages.messageId=(SELECT messages.messageId FROM messages WHERE messages.threadId=threads.threadId ORDER BY messages.creationTime DESC LIMIT 1)'
            . ' WHERE messages.creationTime>=participants.lastActivity;';
        $this->parameters = ['i', $userId];

        $records = $this->functions->runRead();

        if (array_key_exists(1, $records)){
            throw new RuntimeException('Count query returns more than one result', 500);
        }

        return $records[0]['counter'];
    }
}