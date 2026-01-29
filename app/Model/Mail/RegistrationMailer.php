<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

final class RegistrationMailer
{
    public function __construct(private string $mailFrom)
    {
    }

    public function sendWelcome(string $email): void
    {
        $message = new Message();
        $message
            ->setFrom($this->mailFrom)
            ->addTo($email)
            ->setSubject('Vítejte v UltimatePanel')
            ->setBody("Vaše registrace byla úspěšná.\n\nPřihlaste se do panelu a vytvořte svůj první herní server.");

        $mailer = new SendmailMailer();
        $mailer->send($message);
    }
}
