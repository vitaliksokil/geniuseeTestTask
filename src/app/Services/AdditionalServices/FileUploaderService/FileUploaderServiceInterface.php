<?php

namespace App\Services\AdditionalServices\FileUploaderService;

use Illuminate\Http\UploadedFile;
use Jenssegers\Mongodb\Eloquent\Model;

interface FileUploaderServiceInterface
{

    public function upload(array $requestFiles, string $path = null, string $filename = null) : array;
    public function uploadByUrl(string $url, string $path = null, string $filename = null): string;
    public function delete(array $requestFiles): bool;
    public function uploadOrUpdate(string|UploadedFile $file, Model $model, string $property, string $path = null, string $filename = null);

}
