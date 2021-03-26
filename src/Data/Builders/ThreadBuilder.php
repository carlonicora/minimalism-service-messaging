<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\Meta;
use CarloNicora\Minimalism\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\Builder\Objects\RelationshipBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\MessagesDataReader;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\UsersDataReader;
use Exception;

class ThreadBuilder extends AbstractResourceBuilder
{
    /** @var string  */
    public string $type = 'thread';

    /**
     * @param array $data
     * @throws Exception
     */
    public function setAttributes(
        array $data
    ): void
    {
        $this->response->id = $this->encrypter->encryptId($data['threadId']);
    }
    /**
     * @param array $data
     * @throws Exception
     */
    public function setLinks(
        array $data
    ): void
    {
        $this->response->links->add(
            new Link(
                'self',
                $this->path->getUrl()
                . 'threads/'
                . $this->encrypter->encryptId($data['threadId'])
            )
        );

        $this->response->links->add(
            new Link(
                'archive',
                $this->path->getUrl()
                . 'threads/'
                . $this->encrypter->encryptId($data['threadId']),
                new Meta(['method'=>'DELETE'])
            )
        );
    }

    /**
     * @return array|null
     */
    public function getRelationshipReaders(): ?array
    {
        $response = [];

        /** @see UsersDataReader::byThreadId() */
        $response[] = new RelationshipBuilder(
            name: 'members',
            builderClassName: UserBuilder::class,
            function: new DataFunction(
                type: DataFunction::TYPE_LOADER,
                className: UsersDataReader::class,
                functionName: 'byThreadId',
                parameters: ['threadId']
            )
        );

        /** @see MessagesDataReader::byThreadId() */
        $response[] = new RelationshipBuilder(
            name: 'messages',
            builderClassName: MessageBuilder::class,
            function: new DataFunction(
                type: DataFunction::TYPE_LOADER,
                className: MessagesDataReader::class,
                functionName: 'byThreadId',
                parameters: ['threadId', 'fromMessageId']
            )
        );

        return $response;
    }
}