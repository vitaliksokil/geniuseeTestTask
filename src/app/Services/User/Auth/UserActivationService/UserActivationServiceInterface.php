<?php

namespace App\Services\User\Auth\UserActivationService;

use App\DTO\User\Profile\EmailVerificationData;
use App\DTO\User\Profile\SaveEmailVerificationData;
use App\Http\Responses\User\Profile\EmailVerificationResponse;
use App\Http\Responses\User\Profile\SendEmailVerificationResponse;
use App\Models\User\Auth\Activation\UserActivation;
use App\Models\User\User;

interface UserActivationServiceInterface
{
    public function sendActivation(array $data): bool;

    public function confirmActivation(array $data): bool|User;

    public function deleteActivation(array $data): bool;

    public function saveVerification(SaveEmailVerificationData $data): UserActivation;

    public function sendEmailVerification(User $user, string $email = null): SendEmailVerificationResponse;

    public function emailVerification(EmailVerificationData $data): EmailVerificationResponse;
}
