<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data;

use CarloNicora\Minimalism\Factories\ObjectFactory;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractDataObject;

class Thread extends AbstractDataObject
{
    /** @var int  */
    private int $id;

    /**
     * @param ObjectFactory $objectFactory
     * @param array|null $data
     */
    public function __construct(
        ObjectFactory $objectFactory,
        ?array $data = null,
    )
    {
        if ($data !== null) {
            parent::__construct(
                objectFactory: $objectFactory,
                data: $data,
            );
        }
    }

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