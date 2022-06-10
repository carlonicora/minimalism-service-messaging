<?php

namespace CarloNicora\Minimalism\Services\Messaging\Data\Participants\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Interfaces\Sql\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\Messaging\Data\Participants\Databases\ParticipantsTable;

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
    private bool $isArchived = false;

    /** @var int|null */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int|null $lastActivity = null;

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
     * @return int|null
     */
    public function getLastActivity(): int|null
    {
        return $this->lastActivity;
    }

    /**
     * @param int|null $lastActivity
     * @return void
     */
    public function setLastActivity(
        int $lastActivity = null
    ): void
    {
        $this->lastActivity = $lastActivity;
    }

}