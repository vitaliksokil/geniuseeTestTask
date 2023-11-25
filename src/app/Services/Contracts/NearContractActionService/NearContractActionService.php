<?php

namespace App\Services\Contracts\NearContractActionService;

use App\DTO\Chat\ChatCreateData;
use App\DTO\Chat\SendMessageData;
use App\DTO\Contracts\NearContractAction\SaveNearContractAction;
use App\Models\User\User;
use App\Repositories\MongoDB\Contracts\NearContractActionRepository\NearContractActionRepositoryInterface;
use App\Repositories\MongoDB\User\UserRepositoryInterface;
use App\Repositories\NearRPC\Contracts\ContractsRpcRepositoryInterface;
use App\Repositories\NearRPC\TransactionRpc\TransactionRpcRepositoryInterface;
use App\Services\BaseService;
use App\Services\Chat\ChatServiceInterface;

class NearContractActionService extends BaseService implements NearContractActionServiceInterface
{
    public function __construct(
        private readonly NearContractActionRepositoryInterface $repository,
        private readonly TransactionRpcRepositoryInterface $transactionRpcRepository,
        private readonly ContractsRpcRepositoryInterface $contractsRpcRepository,
        private readonly ChatServiceInterface $chatService,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function callback(User $user, string $transactionHashes): array
    {
        $senderId = $user->near_account_id;
        $transactionData = $this->transactionRpcRepository->getTransactionStatus($transactionHashes, $senderId);
        $dto = $this->getNecessaryDataFromRpcResult($transactionData);
        $sender = $this->userRepository->getByNearAccountId($dto->signer_id);
        $contract = $this->contractsRpcRepository->getSpecificContract($dto->contract_id);
        $anotherSide = $this
            ->userRepository
            ->getByNearAccountId(
                $contract->contract->customer_id == $dto->signer_id ?
                    $contract->contract->performer_id : $contract->contract->customer_id
            );
        $chatId = $anotherSide->transform(['chat_id' => [auth()->user()]])->chat_id;
        if (!$chatId) {
            $this->chatService->chatCreate(
                new ChatCreateData([
                    'user_id' => auth()->user()->id,
                    'users' => [$anotherSide->id],
                ])
            );
            $chatId = $anotherSide->transform(['chat_id' => [auth()->user()]])->chat_id;
        }

        $message = $this->chatService->sendMessage(
            new SendMessageData([
                'chat_id' => $chatId,
                'user_id' => $sender->id,
                'is_transaction' => true
            ])
        )['message'];

        $dto = new SaveNearContractAction(array_merge($dto->toArray(),['message_id' => $message->id]));
        return array_merge([
            'chat_id' => $chatId,
            'action_data' => $this->repository->store($dto)
        ]);
    }


    private function getNecessaryDataFromRpcResult(array $rpcResult): SaveNearContractAction
    {
        try {
            $transaction = $rpcResult['transaction'];

            $fCall = $transaction['actions'][0]['FunctionCall'];

            if ($fCall['method_name'] == 'ft_transfer_call') {
                $args_json = json_decode(base64_decode($fCall['args']), true);
                $msgJson = json_decode($args_json['msg'],true);

                return new SaveNearContractAction([
                    'args' => $fCall['args'],
                    'args_json' => $msgJson,
                    'contract_id' => $this->getContractIdFromLogs($rpcResult),
                    'deposit' => $fCall['deposit'],
                    'gas' => $fCall['gas'],
                    'method_name' => camelCaseToSnakeCase($msgJson['method_name']),
                    'label' => snakeCaseToWords(camelCaseToSnakeCase($msgJson['method_name'])),
                    'smart_contract_id' => $args_json['receiver_id'],
                    'hash' => $transaction['hash'],
                    'nonce' => $transaction['nonce'],
                    'public_key' => $transaction['public_key'],
                    'signer_id' => $transaction['signer_id'],
                    'signature' => $transaction['signature'],
                ], false);
            }else{
                $args_json = json_decode(base64_decode($fCall['args']), true);

                return new SaveNearContractAction([
                    'args' => $fCall['args'],
                    'args_json' => $args_json,
                    'contract_id' => $args_json['contract_id'] ?? $this->getContractIdFromLogs($rpcResult),
                    'deposit' => $fCall['deposit'],
                    'gas' => $fCall['gas'],
                    'method_name' => $fCall['method_name'],
                    'label' => snakeCaseToWords($fCall['method_name']),
                    'smart_contract_id' => $transaction['receiver_id'],
                    'hash' => $transaction['hash'],
                    'nonce' => $transaction['nonce'],
                    'public_key' => $transaction['public_key'],
                    'signer_id' => $transaction['signer_id'],
                    'signature' => $transaction['signature'],
                ], false);
            }
        } catch (\Exception $exception) {
            info($exception->getMessage());
            $this->throwValidationError(['transactionHashes' => ['Invalid transaction hashes']]);
        }
    }

    private function getContractIdFromLogs(array $transaction): int {
        $logs = recursiveArraySearch("EVENT_JSON", $transaction);
        $pattern = '/"contract_id":(\d+)/';
        preg_match($pattern, $logs[0], $matches);
        return (int)$matches[1];
    }
}
