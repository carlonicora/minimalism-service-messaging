<?php
namespace CarloNicora\Minimalism\Services\Messaging\Models;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Messaging\Messaging;
use CarloNicora\Minimalism\Services\Messaging\Models\Abstracts\AbstractMessagingModel;
use Exception;
use RuntimeException;

class Threads extends AbstractMessagingModel
{
    /**
     * @param Messaging $messaging
     * @param int|null $fromTime
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        Messaging $messaging,
        ?int $fromTime = null
    ): HttpCode
    {
        $this->requireValidUser();

        $this->document->addResourceList(
            resourceList: $messaging->getThreadsList(
                userId: $this->security->getUserId(),
                fromTime: $fromTime,
            ),
        );

        return HttpCode::Ok;
    }

    /**
     * @param EncrypterInterface $encrypter
     * @param Messaging $messaging
     * @param array $payload
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        EncrypterInterface $encrypter,
        Messaging $messaging,
        array $payload
    ): HttpCode
    {
        $this->requireValidUser();
        $this->validatePayload(
            encrypter: $encrypter,
            payload: $payload,
        );

        $users = $payload['users'];
        $userIds = [];

        foreach ($users as $encryptedUserId) {
            $userIds [] = $encrypter->decryptId($encryptedUserId);
        }

        if (!in_array($this->security->getUserId(), $userIds, true)){
            throw new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'Thread creator should be part of the participants',
            );
        }

        $newThread = $messaging->createThread(
            userIdSender: $this->security->getUserId(),
            userIds: $userIds,
            content: $payload['content']
        );

        $this->document->addResource(resource: $newThread);

        return HttpCode::Created;
    }

    /**
     * @param EncrypterInterface $encrypter
     * @param array $payload
     */
    protected function validatePayload(
        EncrypterInterface $encrypter,
        array $payload,
    ): void
    {
        $encryptedCurrentUserId = $encrypter->encryptId(
            id: $this->security->getUserId(),
        );

        if (empty($payload['users']) || $payload['users'] === [$encryptedCurrentUserId]) {
            throw new RuntimeException('Thread participants missed', 412);
        }

        if (empty($payload['content'])) {
            throw new RuntimeException('Thread content missed', 412);
        }
    }
}