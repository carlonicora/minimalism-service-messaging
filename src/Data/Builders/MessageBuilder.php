<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\Builder\Objects\RelationshipBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\UsersDataReader;
use Exception;

class MessageBuilder extends AbstractResourceBuilder
{
    /** @var string  */
    public string $type = 'message';


    /**
     * @param array $data
     * @throws Exception
     */
    public function setAttributes(
        array $data
    ): void
    {
        $this->response->id = $this->encrypter->encryptId($data['messageId']);
        $this->response->attributes->add('creationTime', $data['creationTime']);
        $this->response->attributes->add('content', $data['content']);
        $this->response->attributes->add('isUnread', $data['unread']===1);
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public function setMeta(
        array $data
    ): void
    {
        parent::setMeta($data);

        $this->response->meta->add('forceGet', true);
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
                . 'messages/'
                . $this->encrypter->encryptId($data['messageId'])
            )
        );
    }

    /**
     * @return array|null
     */
    public function getRelationshipReaders(): ?array
    {
        $response = [];

        /** @see UsersDataReader::byUserId() */
        $response[] = new RelationshipBuilder(
            name: 'sender',
            builderClassName: UserBuilder::class,
            function: new DataFunction(
                type: DataFunction::TYPE_LOADER,
                className: UsersDataReader::class,
                functionName: 'byUserId',
                parameters: ['userId']
            )
        );

        return $response;
    }
}