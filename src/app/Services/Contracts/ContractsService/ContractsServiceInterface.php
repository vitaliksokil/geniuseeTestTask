<?php

namespace App\Services\Contracts\ContractsService;

use App\DTO\Contracts\ContractsListData;
use App\DTO\Contracts\GetDisputesData;
use App\DTO\Contracts\LimitsData;

interface ContractsServiceInterface
{
    public function performerContracts(ContractsListData $dto);

    public function customerContracts(ContractsListData $dto);

    public function disputes(GetDisputesData $dto);

    public function statuses(): array;

    public function getSpecificContract(int $contractId): \stdClass;
}
