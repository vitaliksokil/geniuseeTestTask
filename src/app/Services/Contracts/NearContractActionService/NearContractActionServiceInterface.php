<?php

namespace App\Services\Contracts\NearContractActionService;

use App\Models\User\User;

interface NearContractActionServiceInterface
{
    public function callback(User $user, string $transactionHashes): array;
}
