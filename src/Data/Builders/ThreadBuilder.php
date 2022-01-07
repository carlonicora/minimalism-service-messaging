<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\Meta;
use CarloNicora\Minimalism\Interfaces\Data\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Interfaces\Data\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\Builder\Objects\RelationshipBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\DataReaders\UsersDataReader;
use Exception;

/**
 *@OA\Schema(
 *     schema="threadIdentifier",
 *     description="Thread identifier",
 *     title="Thread resource identifier",
 *     @OA\Property(property="id", type="string", nullable=false, minLength=18, maxLength=18, example="pOGm53wqVeZaB4dDyX"),
 *     @OA\Property(property="type", type="string", nullable=false, example="thread")
 * )
 *
 * @OA\Schema(
 *     schema="thread",
 *     description="Thread resource",
 *     title="Thread",
 *     allOf={@OA\Schema(ref="#/components/schemas/threadIdentifier")},
 *     @OA\Property(property="attributes", ref="#/components/schemas/threadAttributes"),
 *     @OA\Property(property="links", ref="#/components/schemas/threadLinks"),
 *     @OA\Property(property="relationships", ref="#/components/schemas/threadRelationships")
 * )
 *
 * @OA\Schema(
 *     schema="threadAttributes",
 *     description="Thread resource attributes",
 *     title="Thread attributes",
 *     @OA\Property(property="lastMessageTime", type="string", format="date-time", nullable=true, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="lastMessage", type="string", format="", nullable=true, minLength="1", maxLength="100", example="gb9K2dVAMN74Ovz7RG"),
 *     @OA\Property(property="unreadMessages", type="number", format="int32", nullable=false, minimum="1000", maximum="", example="123")
 * )
 *
 * @OA\Schema(
 *     schema="threadLinks",
 *     description="Thread links",
 *     title="Thread resource links",
 *     @OA\Property(property="self", type="string", format="uri", nullable=false, minLength="1", maxLength="100", example="https://api.phlow.com/v2.1/threads/pOGm53wqVeZaB4dDyX"),
 *     @OA\Property(
 *         property="archive",
 *         @OA\Property(property="href", type="string", format="uri", nullable=false, example="https://api.phlow.com/v2.1/threads/pOGm53wqVeZaB4dDyX"),
 *         @OA\Property(
 *             property="archive",
 *             @OA\Property(property="method", type="string", format="", nullable=false, example="DELETE")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="threadRelationships",
 *     description="Thread relationships",
 *     title="Thread resource relationships",
 *     @OA\Property(property="members", type="array", @OA\Items(ref="#/components/schemas/userIdentifier"))
 * )
 *
 * Class ThreadBuilder
 * @package CarloNicora\Minimalism\Services\Messaging\Data\Builders
 */
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

        $this->response->attributes->add('lastMessageTime', $data['createdAt'] ?? null);
        $this->response->attributes->add('lastMessage', $data['content']  ?? null);
        $this->response->attributes->add('unreadMessages', $data['unread']  ?? null);
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
                type: DataFunctionInterface::TYPE_LOADER,
                className: UsersDataReader::class,
                functionName: 'byThreadId',
                parameters: ['threadId']
            )
        );

        return $response;
    }
}