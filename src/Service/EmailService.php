<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendGamesEmail(string $to, string $subject, string $content): void
    {
        $email = (new Email())
            ->from('noreply@videogames.com')
            ->to($to)
            ->subject($subject)
            ->html($content);

        $this->mailer->send($email);
    }
}
