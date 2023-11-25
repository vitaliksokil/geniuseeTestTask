<?php

namespace App\Services\Help;

use App\Models\User\User;

interface HelpServiceInterface
{

    public function send(User $user,array $data);

}
