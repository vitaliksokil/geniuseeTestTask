<?php

namespace App\Services\User\Auth\NearWalletAuthService;

use App\DTO\Auth\GetMessageData;
use App\DTO\Auth\NearWalletSignatureVerificationData;
use App\Http\Responses\User\Auth\AuthUserResponse;

interface NearWalletAuthServiceInterface
{

    public function verifySignature(NearWalletSignatureVerificationData $data): AuthUserResponse;

    public function getMessage(GetMessageData $data): string;

    public function getAccountIdByPublicKey(array $data): string;
}
