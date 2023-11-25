<?php

namespace App\Services\User\Auth\LoginService;

use Illuminate\Http\Request;

interface LoginServiceInterface
{
    public function login(array $data): array;

    public function loginViaProvider(string $provider): string;

    public function loginViaProviderCallback(Request $request,string $provider): array;

}
