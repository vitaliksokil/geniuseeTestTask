<?php

namespace App\Services\Contracts\ContractsService;

use App\DTO\Contracts\ContractsListData;
use App\DTO\Contracts\GetDisputesData;
use App\DTO\Contracts\LimitsData;
use App\Models\User\User;
use App\Repositories\MongoDB\User\UserRepositoryInterface;
use App\Repositories\NearRPC\Contracts\ContractsRpcRepositoryInterface;
use App\Repositories\NearRPC\FTokenRpcRepository\FTokenRpcRepositoryInterface;

class ContractsService implements ContractsServiceInterface
{
    public function __construct(
        private readonly ContractsRpcRepositoryInterface $contractsRpcRepository,
        private readonly FTokenRpcRepositoryInterface $fTokenRpcRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function performerContracts(ContractsListData $dto)
    {
        $result = $this->contractsRpcRepository->getPerformerContracts($dto->account_id, $dto);
        $result = array_map([$this, 'addCurrencyMetadataToContracts'],(array)$result);
        return $result;
    }

    public function customerContracts(ContractsListData $dto)
    {
        $result = $this->contractsRpcRepository->getCustomerContracts($dto->account_id, $dto);
        $result = array_map([$this, 'addCurrencyMetadataToContracts'],(array)$result);
        return $result;
    }

    public function disputes(GetDisputesData $dto)
    {
        $jsonContracts = $this->contractsRpcRepository->getDisputeContracts($dto);
        foreach ($jsonContracts as &$jsonContract) {
            $count = 0;
            $votes = $jsonContract->dispute->votes;
            foreach ($votes as $key => $value) {
                $count += count($value);
            }
            $jsonContract->dispute->votes_count = $count;
            $jsonContract->is_yours = $jsonContract->contract->customer_id == $dto->caller_account_id || $jsonContract->contract->performer_id == $dto->caller_account_id;
        }
        return $jsonContracts;
    }

    public function statuses(): array
    {
        return $this->contractsRpcRepository->statuses();
    }

    public function getSpecificContract(int $contractId): \stdClass
    {
        /* @var User $user */
        $user = auth()->user();

        $contract = $this->contractsRpcRepository->getSpecificContract($contractId);

        $contract->currency_metadata = $this->getCurrencyMetadata($contract);
        $contract->customer = $this->userRepository->getByNearAccountId($contract->contract->customer_id)?->transform(
            ['avatar', 'chat_id' => [auth()->user()]]
        );
        $contract->performer = $this->userRepository->getByNearAccountId(
            $contract->contract->performer_id
        )?->transform(['avatar', 'chat_id' => [$user]]);
        $contract->activity = $this->contractsRpcRepository->getSpecificContractActivity($contractId);
        $contract->possible_actions = $this->contractsRpcRepository->getPossibleActions($user->near_account_id, $contractId);
        return $contract;
    }

    private function getCurrencyMetadata(\stdClass $contract): \stdClass
    {
        if ($contract->payment->ft_contract_id === null){
            $priceInUsd = $this->fTokenRpcRepository->getNativeNearPriceInUsd()['near']['usd'] ?? 0;
            $data = [
                'symbol' => 'NEAR',
                'price_in_usd' => $priceInUsd
            ];
        }else{
            $ftContractId = $contract->payment->ft_contract_id;
            $priceInUsd = $this->fTokenRpcRepository->getTokenPriceInUsd($ftContractId)['price'] ?? 0;
            $symbol = $this->fTokenRpcRepository->getTokenMetadata($ftContractId)['symbol'] ?? '';

            $data = [
                'symbol' => $symbol,
                'price_in_usd' => $priceInUsd
            ];
        }
        return (object)$data;
    }

    private function addCurrencyMetadataToContracts(\stdClass $item) {
        $item->currency_metadata = $this->getCurrencyMetadata($item);
        return $item;
    }
}
