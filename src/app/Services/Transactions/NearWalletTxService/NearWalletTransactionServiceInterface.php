<?php

namespace App\Services\Transactions\NearWalletTxService;

use App\Models\Transactions\NearWalletTransaction;
use App\Models\User\User;

interface NearWalletTransactionServiceInterface
{
    public function saveTransaction(User $user, string $transactionHashes): NearWalletTransaction;
}
