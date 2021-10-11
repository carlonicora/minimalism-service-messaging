<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\Builder\Objects\RelationshipBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\ThreadsDataReader;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\UsersDataReader;
use Exception;

/**
 * @OA\Schema(
 *     schema="messageIdentifier",
 *     description="Message identifier",
 *     title="Message resource identifier",
 *     @OA\Property(property="id", type="string", nullable=false, minLength=18, maxLength=18, example="p6Da23dZNybX4Wz95K"),
 *     @OA\Property(property="type", type="string", nullable=false, example="message")
 * )
 *
 * @OA\Schema(
 *     schema="message",
 *     description="Message resource",
 *     title="Message",
 *     allOf={@OA\Schema(ref="#/components/schemas/messageIdentifier")},
 *     @OA\Property(property="attributes", ref="#/components/schemas/messageAttributes"),
 *     @OA\Property(property="links", ref="#/components/schemas/messageLinks"),
 *     @OA\Property(property="relationships", ref="#/components/schemas/messageRelationships"),
 *     @OA\Property(property="meta", ref="#/components/schemas/messageMeta")
 * )
 *
 * @OA\Schema(
 *     schema="messageAttributes",
 *     description="Message resource attributes",
 *     title="Message attributes",
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=false, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="content", type="string", format="", nullable=false, minLength="1", maxLength="100", example="A content"),
 *     @OA\Property(property="isUnread", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="messageLinks",
 *     description="Message links",
 *     title="Message resource links",
 *     @OA\Property(property="self", type="string", format="uri", nullable=false, minLength="1", maxLength="100", example="https://api.phlow.com/v2.1/threads/pOGm53wqVeZaB4dDyX/messages/p6Da23dZNybX4Wz95K")
 * )
 *
 * @OA\Schema(
 *     schema="messageRelationships",
 *     description="Message relationships",
 *     title="Message resource relationships",
 *     @OA\Property(property="sender", ref="#/components/schemas/userIdentifier")
 * )
 *
 * Class MessageBuilder
 * @package CarloNicora\Minimalism\Services\Messaging\Data\Builders
 */
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
        $this->response->attributes->add('createdAt', $data['createdAt']);
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
                type: DataFunctionInterface::TYPE_LOADER,
                className: UsersDataReader::class,
                functionName: 'byUserId',
                parameters: ['userId']
            )
        );

        /** @see ThreadsDataReader::byMessageId() */
        $response[] = new RelationshipBuilder(
            name: 'thread',
            builderClassName: ThreadBuilder::class,
            function: new DataFunction(
                type: DataFunction::TYPE_LOADER,
                className: ThreadsDataReader::class,
                functionName: 'byMessageId',
                parameters: ['messageId']
            )
        );

        return $response;
    }
}