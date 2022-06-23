<?php

namespace CarloNicora\Minimalism\Services\Messaging\Data\Messages\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Messaging\Data\Abstracts\AbstractMessagingBuilder;
use CarloNicora\Minimalism\Services\Messaging\Data\Messages\DataObjects\Message;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
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
class MessageBuilder extends AbstractMessagingBuilder
{
    /**
     * @param Message $data
     * @return ResourceObject
     * @throws Exception
     */
    public function buildResource(
        ResourceableDataInterface $data
    ): ResourceObject
    {
        $response = new ResourceObject(
            type: 'message',
            id: $this->encrypter->encryptId($data->getId()),
        );

        $response->attributes->add(name: 'createdAt', value: $data->getCreatedAt());
        $response->attributes->add(name: 'content', value: $data->getContent());
        $response->attributes->add(name: 'isUnread', value: $data->isUnread());

        $response->links->add(new Link(
            name: 'self',
            href: $this->path->getUrl()
            . 'messages/'
            . $this->encrypter->encryptId($data->getId())
        ));

        $response->relationship(relationshipKey: 'thread')->links->add(new Link(
            name: 'related',
            href: $this->path->getUrl()
            . 'threads'
            . '/' . $this->encrypter->encryptId($data->getThreadId()),
        ));

        $response->relationship(relationshipKey: 'author')->links->add(new Link(
            name: 'related',
            href: $this->path->getUrl()
            . 'users'
            . '/' . $this->encrypter->encryptId($data->getUserId()),
        ));

        return $response;
    }
}