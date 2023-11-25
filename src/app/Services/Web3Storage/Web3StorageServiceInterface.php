<?php

namespace App\Services\Web3Storage;

use App\DTO\Web3Storage\GetWeb3StorageFileData;
use App\Http\Responses\Web3Storage\GetWeb3StorageFileResponse;

interface Web3StorageServiceInterface
{
    public function getFile(GetWeb3StorageFileData $data): GetWeb3StorageFileResponse;
}
