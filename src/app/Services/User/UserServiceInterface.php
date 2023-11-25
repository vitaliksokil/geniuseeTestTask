<?php

namespace App\Services\User;

use App\Http\Responses\User\Profile\SendEmailVerificationResponse;
use App\Models\User\GalleryItem;
use App\Models\User\User;

interface UserServiceInterface
{
    public function checkEmailExistence(string $email, string $ignoreUserId = null): bool;

    public function checkPhoneExistence(array $phoneNumber, string $ignoreUserId = null): bool;

    public function checkNicknameExistence(string $nickName, string $ignoreUserId = null): bool;

    public function updateForgotPassword(User $user, array $data): bool;

    public function updatePassword(User $user, array $data): User;

    public function updateProfile(User $user, array $data): User;

    public function deleteGalleryFile(User $user, GalleryItem $galleryItem): User;

    public function updateEmail(User $user, array $data): User;

    public function updatePhone(User $user, array $data): User;

    public function updateLocation(User $user, array $data): User;

    public function getAnotherUserProfile(string $nicknameOrId): User;

}
