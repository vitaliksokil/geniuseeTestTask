<?php

namespace App\Services\Media;

use App\Models\Media\Media;

interface DownloadMediaServiceInterface
{
    public function download(string $id, string $modelType): Media;
}
