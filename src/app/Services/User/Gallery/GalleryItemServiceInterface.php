<?php

namespace App\Services\User\Gallery;

use App\Models\User\GalleryItem;
use App\Models\User\User;

interface GalleryItemServiceInterface
{
    public function uploadGallery(User $user, array $gallery): void;

    public function deleteGalleryItem(GalleryItem $galleryItem): void;
}
