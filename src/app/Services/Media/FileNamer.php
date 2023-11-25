<?php

namespace App\Services\Media;

use Spatie\MediaLibrary\Conversions\Conversion;

class FileNamer extends \Spatie\MediaLibrary\Support\FileNamer\FileNamer
{
    public function originalFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }

    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        $strippedFileName = $this->originalFileName($fileName);

        return "{$strippedFileName}-{$conversion->getName()}";
    }

    public function responsiveFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }
}
