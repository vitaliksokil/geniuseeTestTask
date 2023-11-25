<?php

namespace App\Services\User\Auth\LogoutService;

interface LogoutServiceInterface
{
    public function currentUserLogout(): bool;
}
