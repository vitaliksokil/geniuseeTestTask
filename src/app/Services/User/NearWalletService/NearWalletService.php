<?php

namespace App\Services\User\NearWalletService;

use App\DTO\User\Wallets\ConnectWalletData;
use App\DTO\User\Wallets\DeleteWalletData;
use App\DTO\User\Wallets\GetWalletData;
use App\DTO\User\Wallets\GetWalletHistoryData;
use App\DTO\User\Wallets\SetPrimaryData;
use App\DTO\User\Wallets\UpdateWalletData;
use App\Http\Responses\User\Wallet\GetWalletResponse;
use App\Models\User\NearWallet;
use App\Models\User\User;
use App\Repositories\Interfaces\User\NearWalletRepositoryInterface;
use App\Repositories\MongoDB\Transactions\NearWalletTxRepository\NearWalletTransactionRepositoryInterface;
use App\Repositories\NearRPC\User\AccountRpcRepository\AccountRpcRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class NearWalletService extends BaseService implements NearWalletServiceInterface
{

    public function __construct(
        private readonly AccountRpcRepositoryInterface $accountRpcRepository,
        private readonly NearWalletTransactionRepositoryInterface $nearWalletTransactionRepository
    ) {
    }

    public function connect(User $user, ConnectWalletData $dto): array
    {
        // todo add signature verification
    }

    public function deleteWallet(DeleteWalletData $dto): array
    {
        // todo maybe delete of wallet
    }

    public function getWallet(GetWalletData $dto): GetWalletResponse
    {
        return $dto->user->transform(['wallet_balance'])->only('wallet_balance')['wallet_balance'];
    }

    public function getWalletHistory(GetWalletHistoryData $dto): array
    {
        return isset($dto->user->near_account_id) ? $this->accountRpcRepository->getAccountHistory($dto->user->near_account_id, $dto->cursor_timestamp) : [];
    }

    public function getWalletActivity(User $user): LengthAwarePaginator
    {
        return $this->nearWalletTransactionRepository->getWalletActivity($user->near_account_id);
    }

    public function getWalletTokensByAccountId(string $accountId, bool $clearCache = false): array
    {
        $cacheKey = 'ft_tokens_balance_' . $accountId;

        if ($clearCache){
            Cache::forget($cacheKey);
        }

        $tokens = Cache::get($cacheKey);
        if (!$tokens){
            $nearBalance = $this->accountRpcRepository->getAccountNativeBalance($accountId);
            $symbol = 'NEAR';
            $near = [
                'spec' => null,
                'name' => $symbol,
                'symbol' => $symbol,
                'icon' => '<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg%22%3E
<path d="M28.5466 2.28889L21.2436 13.1389C20.7386 13.8778 21.7098 14.7722 22.409 14.15L29.5954 7.88889C29.7897 7.73333 30.0616 7.85 30.0616 8.12222V27.6833C30.0616 27.9556 29.712 28.0722 29.5566 27.8778L7.803 1.82222C7.10377 0.966667 6.09378 0.5 4.96726 0.5H4.19034C2.17037 0.5 0.5 2.17222 0.5 4.23333V31.7667C0.5 33.8278 2.17037 35.5 4.22919 35.5C5.5111 35.5 6.71532 34.8389 7.41454 33.7111L14.7175 22.8611C15.2225 22.1222 14.2514 21.2278 13.5522 21.85L6.36571 28.0722C6.17148 28.2278 5.89956 28.1111 5.89956 27.8389V8.31667C5.89956 8.04444 6.24917 7.92778 6.40455 8.12222L28.1582 34.1778C28.8574 35.0333 29.9062 35.5 30.9939 35.5H31.7708C33.8296 35.5 35.5 33.8278 35.5 31.7667V4.23333C35.5 2.17222 33.8296 0.5 31.7708 0.5C30.4501 0.5 29.2458 1.16111 28.5466 2.28889Z" fill="black"/>
</svg>',
                'reference' => null,
                'reference_hash' => null,
                'decimals' => 24,
                'balance' => $nearBalance['amount'],
                'account_id' => null,
                'price' => [
                    'price' => $nearBalance['price']['usd'],
                    'decimal' => 24,
                    'symbol' => $symbol,
                ],
                "total_in_usd" => $nearBalance['total_in_usd']
            ];
            $tokens = $this->accountRpcRepository->getAccountTokensWithBalances($accountId);
            array_unshift($tokens, $near);
            Cache::add($cacheKey,$tokens,now()->addMinutes(5));
        }

        return [
            'tokens' => $tokens,
        ];
    }
}
