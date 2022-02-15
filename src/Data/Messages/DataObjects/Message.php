<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Messages\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\Databases\MessagesTable;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;

#[DbTable(tableClass: MessagesTable::class)]
class Message implements SqlDataObjectInterface, ResourceableDataInterface
{
    use SqlDataObjectTrait;

    /** @var int  */
    #[DbField(field: MessagesTable::messageId)]
    private int $id;

    /** @var int  */
    #[DbField]
    private int $threadId;

    /** @var int  */
    #[DbField]
    private int $userId;

        /** @var string  */
    #[DbField]
    private string $content;

    /** @var int  */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    #[DbField]
    private bool $unread=false;

    /** @return int */
    public function getId(): int{return $this->id;}

    /** @param int $id */
    public function setId(int $id): void{$this->id = $id;}

    /** @return int */
    public function getThreadId(): int{return $this->threadId;}

    /** @param int $threadId */
    public function setThreadId(int $threadId): void{$this->threadId = $threadId;}

    /** @return int */
    public function getUserId(): int{return $this->userId;}

    /** @param int $userId */
    public function setUserId(int $userId): void{$this->userId = $userId;}

    /** @return string */
    public function getContent(): string{return $this->content;}

    /** @param string $content */
    public function setContent(string $content): void{$this->content = $content;}

    /** @return int */
    public function getCreatedAt(): int{return $this->createdAt;}

    /** @return bool */
    public function isUnread(): bool{return $this->unread;}
}