<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Threads\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\Databases\ThreadsTable;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Users\Data\Users\DataObjects\User;

#[DbTable(tableClass: ThreadsTable::class)]
class Thread implements SqlDataObjectInterface, ResourceableDataInterface
{
    use SqlDataObjectTrait;

    /** @var int  */
    #[DbField(field: ThreadsTable::threadId)]
    private int $id;

    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private ?int $createdAt=null;

    #[DbField]
    private ?string $content=null;

    #[DbField]
    private ?string $unread=null;

    /** @var User[]  */
    private array $users=[];

    /** @return int */
    public function getId(): int{return $this->id;}

    /** @param int $id */
    public function setId(int $id): void{$this->id = $id;}

    /** @return int|null */
    public function getLastMessageTime(): ?int{return $this->createdAt;}

    /** @return string|null */
    public function getLastMessage(): ?string{return $this->content;}

    /** @return string|null */
    public function getUnreadMessages(): ?string{return $this->unread;}

    /** @return User[] */
    public function getUsers(): array{return $this->users;}

    /** @param User[] $users */
    public function setUsers(array $users): void{$this->users = $users;}

    /** @param User $user */
    public function addUser(User $user): void{$this->users[] = $user;}
}