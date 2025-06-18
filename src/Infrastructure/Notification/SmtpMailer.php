<?php

// src/Infrastructure/Notification/SmtpMailer.php

namespace App\Infrastructure\Notification;

use App\Domain\Notification\EmailSenderInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SmtpMailer implements EmailSenderInterface
{
    public function __construct(private MailerInterface $mailer) {}

    public function send(string $to, string $subject, string $body): void
    {
        $email = (new Email())
            ->from('daicrela@gmail.com')
            ->to($to)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }
}
