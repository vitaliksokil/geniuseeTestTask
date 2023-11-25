<?php

namespace App\Services\User\Auth\ForgotPasswordService;

interface ForgotPasswordServiceInterface
{
    public function sendForgotPassword(array $data): string;

    public function verify(array $data): array;

    public function changePassword(array $data): bool;
}
