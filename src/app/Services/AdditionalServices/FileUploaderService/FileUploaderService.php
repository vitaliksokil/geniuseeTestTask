<?php

namespace App\Services\AdditionalServices\FileUploaderService;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jenssegers\Mongodb\Eloquent\Model;

class FileUploaderService implements FileUploaderServiceInterface
{

    public function upload(array $requestFiles, string $path = null, string $filename = null): array
    {

        $files = [];

        $path = (!is_null($path))? $path : 'files';

        foreach ($requestFiles as $file) {
            $explode = explode('/', $file->getMimeType());
            $mimeType = end($explode);
            array_pop($explode);

            $name = (!is_null($filename))
                ? implode('.',[$filename . '_' . now()->timestamp,$mimeType])
                : implode('.',[Str::random(40),$mimeType]);

            Storage::putFileAs($path, $file, $name);
            $files[] = $path . '/' . $name;
        }

        return $files;

    }

    public function uploadByUrl(string $url, string $path = null, string $filename = null) : string
    {

        $name = (!is_null($filename))
                ? $filename . '_' . now()->timestamp
                : Str::random(40);

        $name = $name . '.jpeg';

        $filepath = $path.'/'.$name;


        $img = createUploadedFileFromUrl($url);

        Storage::putFileAs($path, $img, $name);

        return $filepath;
    }

    public function delete(array $requestFiles): bool
    {

        foreach ($requestFiles as $file) {
            Storage::delete($file);
        }

        return true;

    }

    public function uploadOrUpdate(string|UploadedFile $file, Model $model, string $property, string $path = null, string $filename = null)
    {
        if (is_file($file)) {

            try {
                $result = $this->upload([$file], $path, $filename)[0];

                if (!is_null($model->{$property})) {
                    $this->delete([$model->{$property}]);
                }

                return $result;

            } catch (\Exception $e) {
                logger()->error($e);
                return null;
            }

        }

        return $file;
    }

}
