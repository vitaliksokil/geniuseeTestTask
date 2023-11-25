<?php

namespace App\Services\User\Auth\RegistrationService;

interface RegistrationServiceInterface
{
    public function registration(array $data): array;
}
