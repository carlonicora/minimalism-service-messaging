<?php
namespace CarloNicora\Minimalism\Services\Messaging\Data\Threads\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\Meta;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\Threads\DataObjects\Thread;
use CarloNicora\Minimalism\Services\Messaging\Enums\MessagingDictionary;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
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
class ThreadBuilder extends AbstractMessagingBuilder
{
    /**
     * @param Thread $data
     * @return ResourceObject
     * @throws Exception
     */
    public function buildResource(
        ResourceableDataInterface $data
    ): ResourceObject
    {
        $encryptedId = $this->encrypter->encryptId($data->getId());

        $response = new ResourceObject(
            type: 'thread',
            id: $encryptedId,
        );

        $response->attributes->add(name: 'lastMessageTime', value: $data->getLastMessageTime());
        $response->attributes->add(name: 'lastMessage', value: $data->getLastMessage());
        $response->attributes->add(name: 'unreadMessages', value: $data->getUnreadMessages());

        $threadUrl = $this->path->getUrl() . MessagingDictionary::Thread->getEndpoint() . '/' . $encryptedId;

        $response->links->add( new Link(
            name: 'self',
            href: $threadUrl
        ));

        $response->links->add(new Link(
            name: 'archive',
            href: $threadUrl,
            meta: new Meta(['method'=>'DELETE'])
        ));

        $response->relationship(relationshipKey: 'participants')->links->add(new Link(
            name: 'related',
            href: $this->users->getUserUrlByIds($data->getUsers()),
        ));

        return $response;
    }
}