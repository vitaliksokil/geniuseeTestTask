<?php

namespace App\Services\Help;

use App\Mail\Help\SendMessageMail;
use App\Models\User\User;
use App\Repositories\MongoDB\Help\HelpRepositoryInterface;
use App\Services\AdditionalServices\MailService\MailServiceInterface;
use App\Services\BaseService;

class HelpService extends BaseService implements HelpServiceInterface
{

    public function __construct(
        private MailServiceInterface $mailService,
        private HelpRepositoryInterface $helpRepositoryInterface
        ){}

    public function send(User $user, array $data)
    {
        $this->validate($data,[
            'message' => 'required|max:10000'
        ]);

        $this->mailService->send(config('mail.support_email'),new SendMessageMail($data['message']));

        return $this->helpRepositoryInterface->create([
            'user_id' => $user->id,
            'message' => $data['message']
        ]);

    }
}
