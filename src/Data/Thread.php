<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data;

use CarloNicora\Minimalism\Interfaces\Data\Abstracts\AbstractDataObject;

class Thread extends AbstractDataObject
{
    /** @var int  */
    private int $id;

    /**
     * @param array $data
     * @return void
     */
    public function import(
        array $data,
    ): void
    {
        $this->id = $data['threadId'];
    }

    /**
     * @return array
     */
    public function export(
    ): array
    {
        $response = parent::export();

        $response['threadId'] = $this->id ?? null;

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