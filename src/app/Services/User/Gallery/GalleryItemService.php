<?php

namespace App\Services\User\Gallery;

use App\Models\User\GalleryItem;
use App\Models\User\User;
use App\Repositories\MongoDB\User\GalleryRepository\GalleryItemRepositoryInterface;
use App\Services\BaseService;

class GalleryItemService extends BaseService implements GalleryItemServiceInterface
{
    public function __construct(private readonly GalleryItemRepositoryInterface $galleryItemRepository)
    {
    }

    public function uploadGallery(User $user, array $gallery): void
    {
        $this->galleryItemRepository->uploadGallery($user, $gallery);
    }

    public function deleteGalleryItem(GalleryItem $galleryItem): void
    {
        $this->galleryItemRepository->deleteByIds([$galleryItem->id]);
    }
}
