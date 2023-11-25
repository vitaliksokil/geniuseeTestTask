<?php

namespace App\Services\AdditionalServices\MailService;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService implements MailServiceInterface
{

    public function send(string $email, Mailable $mail)
    {
        Mail::to($email)->send($mail);
    }
}
