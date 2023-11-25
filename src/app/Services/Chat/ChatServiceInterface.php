<?php

namespace App\Services\Chat;

use App\DTO\Chat\ChatCloseData;
use App\DTO\Chat\ChatCreateData;
use App\DTO\Chat\ChatOpenData;
use App\DTO\Chat\SendMessageData;

interface ChatServiceInterface
{
    public function chatCreate(ChatCreateData $dto);

    public function chatOpen(ChatOpenData $dto);

    public function sendMessage(SendMessageData $dto);

    public function chatClose(ChatCloseData $dto);

}
