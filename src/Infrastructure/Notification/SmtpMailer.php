<?php

// src/Infrastructure/Notification/SmtpMailer.php

namespace App\Infrastructure\Notification;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class SmtpMailer extends AbstractEmailSender
{
    private MailerInterface $mailer;

    public function __construct(
        MailerInterface $mailer, 
        string $fromEmail = 'daicrela@gmail.com',
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($fromEmail, $logger);
        $this->mailer = $mailer;
    }

    protected function doSend(string $to, string $subject, string $body): void
    {
        $email = (new Email())
            ->from($this->getFromEmail())
            ->to($to)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }
}
