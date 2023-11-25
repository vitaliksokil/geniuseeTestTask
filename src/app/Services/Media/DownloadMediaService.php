<?php

namespace App\Services\Media;

use App\Models\Media\Media;
use App\Repositories\MongoDB\Media\MediaRepositoryInterface;
use App\Services\BaseService;

class DownloadMediaService extends BaseService implements DownloadMediaServiceInterface
{
    public function __construct(private readonly MediaRepositoryInterface $mediaRepository)
    {
    }

    public function download(string $id, string $modelType): Media
    {
        $data = ['model_type' => $modelType];

        $this->validate($data,[
            'model_type' => 'required|in:'.implode(',',Media::ALLOWED_DOWNLOAD_MODELS)
        ]);

        $media = $this->mediaRepository->getByIdAndModelType($id, $modelType);

        $this->authorize('canDownload', [$media, $modelType]);

        return $media;
    }
}
