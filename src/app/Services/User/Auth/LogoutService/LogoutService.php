<?php

namespace App\Services\User\Auth\LogoutService;

use App\Services\BaseService;

class LogoutService extends BaseService implements LogoutServiceInterface
{

    public function currentUserLogout(): bool
    {
        return auth()->user()->currentAccessToken()->delete();
    }
}
