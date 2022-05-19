<?php

namespace CarloNicora\Minimalism\Services\Messaging\Data\Participants\DataObject;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\Databases\ParticipantsTable;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;

#[DbTable(tableClass: ParticipantsTable::class)]
class Participant implements SqlDataObjectInterface
{
    use SqlDataObjectTrait;

    /** @var int */
    #[DbField(field: ParticipantsTable::threadId)]
    private int $threadId;

    /** @var int */
    #[DbField]
    private int $userId;

    /** @var bool */
    #[DbField(fieldType: DbFieldType::Bool)]
    private bool $isArchived;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $lastActivity;

    /**
     * @return int
     */
    public function getThreadId(): int
    {
        return $this->threadId;
    }

    /**
     * @param int $threadId
     */
    public function setThreadId(int $threadId): void
    {
        $this->threadId = $threadId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->isArchived;
    }

    /**
     * @param bool $isArchived
     */
    public function setIsArchived(bool $isArchived): void
    {
        $this->isArchived = $isArchived;
    }

    /**
     * @return int
     */
    public function getLastActivity(): int
    {
        return $this->lastActivity;
    }

    /**
     * @param int $lastActivity
     */
    public function setLastActivity(int $lastActivity): void
    {
        $this->lastActivity = $lastActivity;
    }

}