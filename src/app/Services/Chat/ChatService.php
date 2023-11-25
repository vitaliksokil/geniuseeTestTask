<?php

namespace App\Services\Chat;

use App\DTO\Chat\ChatCloseData;
use App\DTO\Chat\ChatCreateData;
use App\DTO\Chat\ChatOpenData;
use App\DTO\Chat\SendMessageData;
use App\Repositories\MongoDB\Chat\ChatRepository\ChatRepositoryInterface;
use App\Services\BaseService;

class ChatService extends BaseService implements ChatServiceInterface
{

    public function __construct(private readonly ChatRepositoryInterface $chatRepository) {}

    public function chatCreate(ChatCreateData $dto)
    {
        return $this->chatRepository->chatCreate($dto);
    }

    public function chatOpen(ChatOpenData $dto)
    {
        return $this->chatRepository->chatOpen($dto);
    }

    public function sendMessage(SendMessageData $dto)
    {
        return $this->chatRepository->sendMessage($dto);
    }

    public function chatClose(ChatCloseData $dto)
    {
        return $this->chatRepository->chatClose($dto);
    }
}
