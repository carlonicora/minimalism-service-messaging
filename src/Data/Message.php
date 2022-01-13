<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractDataObject;

class Message extends AbstractDataObject
{
    /** @var int  */
    private int $id;

    /** @var int  */
    private int $threadId;

    /** @var int  */
    private int $userId;

    /** @var int  */
    private int $createdAt;

    /** @var string  */
    private string $content;

    /**
     * @param array $data
     * @return void
     */
    public function import(
        array $data,
    ): void
    {
        $this->id = $data['messageId'];
        $this->threadId = $data['threadId'];
        $this->userId = $data['userId'];
        $this->createdAt = strtotime($data['createdAt']);
        $this->content = $data['content'];
    }

    /**
     * @return array
     */
    public function export(
    ): array
    {
        $response = parent::export();

        $response['messageId'] = $this->id ?? null;
        $response['threadId'] = $this->threadId;
        $response['userId'] = $this->userId;
        $response['createdAt'] = date('Y-m-d H:i:s', $this->createdAt ?? time());
        $response['content'] = $this->content;

        return $response;
    }

    /**
     * @return int
     */
    public function getId(
    ): int
    {
        return $this->id;
    }
}