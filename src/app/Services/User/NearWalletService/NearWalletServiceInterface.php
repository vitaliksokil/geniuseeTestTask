<?php

namespace App\Services\User\NearWalletService;

use App\DTO\User\Wallets\ConnectWalletData;
use App\DTO\User\Wallets\DeleteWalletData;
use App\DTO\User\Wallets\GetWalletData;
use App\DTO\User\Wallets\GetWalletHistoryData;
use App\Http\Responses\User\Wallet\GetWalletResponse;
use App\Models\User\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface NearWalletServiceInterface
{
    public function connect(User $user, ConnectWalletData $dto): array;

    public function deleteWallet(DeleteWalletData $dto): array;

    public function getWallet(GetWalletData $dto): GetWalletResponse;

    public function getWalletHistory(GetWalletHistoryData $dto): array;

    public function getWalletActivity(User $user): LengthAwarePaginator;

    public function getWalletTokensByAccountId(string $accountId, bool $clearCache = false): array;
}
