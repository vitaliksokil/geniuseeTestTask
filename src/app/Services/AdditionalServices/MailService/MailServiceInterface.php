<?php

namespace App\Services\AdditionalServices\MailService;

use Illuminate\Mail\Mailable;

interface MailServiceInterface
{
    public function send(string $email, Mailable $mail);
}
