<?php

namespace App\Services\Transactions\NearWalletTxService;

use App\DTO\Chat\ChatCreateData;
use App\DTO\Chat\SendMessageData;
use App\DTO\Transactions\NearWalletTransaction\CreateNearWalletTransactionData;
use App\Models\Transactions\NearWalletTransaction;
use App\Models\User\User;
use App\Repositories\MongoDB\Transactions\NearWalletTxRepository\NearWalletTransactionRepositoryInterface;
use App\Repositories\MongoDB\User\UserRepositoryInterface;
use App\Repositories\NearRPC\FTokenRpcRepository\FTokenRpcRepositoryInterface;
use App\Repositories\NearRPC\TransactionRpc\TransactionRpcRepositoryInterface;
use App\Services\Chat\ChatServiceInterface;

class NearWalletTransactionService implements NearWalletTransactionServiceInterface
{
    public function __construct(
        private readonly TransactionRpcRepositoryInterface $transactionRpcRepository,
        private readonly NearWalletTransactionRepositoryInterface $transactionRepository,
        private readonly ChatServiceInterface $chatService,
        private readonly FTokenRpcRepositoryInterface $fTokenRpcRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function saveTransaction(
        User $user, string $transactionHashes
    ): NearWalletTransaction {
        $senderId = $user->near_account_id;
        $transactionData = $this->transactionRpcRepository->getTxStatusFromHashesByMethodName($transactionHashes, $senderId, 'ft_transfer');
        $dto = $this->getNecessaryDataFromRpcResult($transactionData);
        $newOwner = $this->userRepository->getByNearAccountId($dto->new_owner_id);
        $chatId = $newOwner->transform(['chat_id' => [$user]])->chat_id;
        if (!$chatId) {
            $chatId = $this->chatService->chatCreate(new ChatCreateData([
                'user_id' => $user->id,
                'users' => [$newOwner->id],
            ]))->id;
        }
        $message = $this->chatService->sendMessage(
            new SendMessageData([
                'chat_id' => $chatId,
                'user_id' => $user->id,
                'is_transaction' => true
            ])
        )['message'];

        $dto = new CreateNearWalletTransactionData(array_merge($dto->toArray(),['message_id' => $message->id]));

        return $this->transactionRepository->create($dto->toArray());
    }

    private function getNecessaryDataFromRpcResult(array $rpcResult): CreateNearWalletTransactionData
    {
        $transaction = $rpcResult['transaction'];
        $tokenName = '';

        if (isset($rpcResult['receipts_outcome'][0]['outcome']['logs']) && !empty($rpcResult['receipts_outcome'][0]['outcome']['logs'])) {
            // for FT tokens
            $logs = $rpcResult['receipts_outcome'][0]['outcome']['logs'];

            try {
                $logData = json_decode(explode('EVENT_JSON:', $logs[0])[1]);
                $event = $logData->event;
                $oldOwnerId = $logData->data[0]->old_owner_id;
                $newOwnerId = $logData->data[0]->new_owner_id;
                $amount = $logData->data[0]->amount;
            }catch (\Exception $exception) {
                $fCall = $transaction['actions'][0]['FunctionCall'];
                $args = json_decode(base64_decode($fCall['args']));
                $event = $fCall['method_name'];
                $oldOwnerId = $transaction['signer_id'];
                $newOwnerId = $args->receiver_id;
                $amount = $args->amount;
            }

            $contractId = $transaction['receiver_id'];

            $metadata = $this->fTokenRpcRepository->getTokenMetadata($contractId);
            $tokenName = $metadata['name'];
            $symbol = $metadata['symbol'];
            $decimals = $metadata['decimals'];

            $price = $this->fTokenRpcRepository->getTokenPriceInUsd($contractId);

            $priceInUsd = $price['price'] ?? null;
            $totalInUsd = isset($priceInUsd) ? getTotalBalance($amount, $decimals, $priceInUsd) : null;
        } else {
            // for Native Token
            $event = array_key_first($transaction['actions'][0]);
            $oldOwnerId = $transaction['signer_id'];
            $newOwnerId = $transaction['receiver_id'];
            $amount = array_shift($transaction['actions'][0])['deposit'];
            $tokenName = 'NEAR';
            $symbol = 'NEAR';
            $decimals = 24;

            $price = $this->fTokenRpcRepository->getNativeNearPriceInUsd();
            $priceInUsd = $price['near']['usd'] ?? null;
            $totalInUsd = isset($priceInUsd) ? getTotalBalance($amount, $decimals, $priceInUsd) : null;
        }


        $hash = $transaction['hash'];
        $nonce = $transaction['nonce'];
        $publicKey = $transaction['public_key'];
        $signature = $transaction['signature'];
        $signer_id = $transaction['signer_id'];

        $oldUserId = $this->userRepository->getByNearAccountId($oldOwnerId)?->id;
        $newUserId = $this->userRepository->getByNearAccountId($newOwnerId)?->id;

        return new CreateNearWalletTransactionData([
            'event' => $event,
            'old_owner_id' => $oldOwnerId,
            'new_owner_id' => $newOwnerId,
            'old_user_id' => $oldUserId,
            'new_user_id' => $newUserId,
            'amount' => $amount,
            'contract_id' => $contractId ?? '',
            'hash' => $hash,
            'nonce' => $nonce,
            'public_key' => $publicKey,
            'signature' => $signature,
            'signer_id' => $signer_id,
            'token_name' => $tokenName,
            'total_in_usd' => $totalInUsd,
            'price_in_usd' => $priceInUsd,
            'decimals' => $decimals,
            'symbol' => $symbol,
        ], false);
    }
}
