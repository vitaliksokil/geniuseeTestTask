<?php

namespace App\Services\Web3Storage;

use App\DTO\Web3Storage\GetWeb3StorageFileData;
use App\Enums\Web3Storage\FileTypesEnum;
use App\Http\Responses\Web3Storage\GetWeb3StorageFileResponse;
use App\Services\BaseService;

class Web3StorageService extends BaseService implements Web3StorageServiceInterface
{

    public function getFile(GetWeb3StorageFileData $data): GetWeb3StorageFileResponse
    {
        if (preg_match('~http(s?):\/?\/?~', $data->original_url,$matches)) {
            // checking if it was http or https and setting the correct replacement
            $replacement = isset($matches[1]) && $matches[1] == 's' ? 'https://' : 'http://';
            $fileLink = preg_replace("~$matches[0]~", $replacement, $data->original_url);
        }

        $extension = pathinfo($data->file_name, PATHINFO_EXTENSION);
        $enum = FileTypesEnum::getFileTypeEnumByValue($data->type);
        $contentType = $enum->getContentTypeByExtension($extension);

        return new GetWeb3StorageFileResponse(
            headers: [
                'Content-Type' => $contentType,
                'Access-Control-Allow-Origin' => '*',
            ],
            fileLink: $fileLink,
            fileName: $data->file_name
        );
    }
}
