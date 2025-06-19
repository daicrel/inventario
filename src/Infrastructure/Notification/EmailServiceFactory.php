<?php

// src/Infrastructure/Notification/EmailServiceFactory.php

namespace App\Infrastructure\Notification;

use App\Domain\Notification\EmailSenderInterface;
use InvalidArgumentException;

class EmailServiceFactory
{
    public const SMTP = 'smtp';
    public const SES = 'ses';
    public const SENDGRID = 'sendgrid';
    public const MAILGUN = 'mailgun';
    public const LOG = 'log';

    private SmtpMailer $smtpMailer;
    private ?SesMailer $sesMailer;
    private ?SendGridMailer $sendGridMailer;
    private ?MailgunMailer $mailgunMailer;
    private LogMailer $logMailer;

    public function __construct(
        SmtpMailer $smtpMailer,
        LogMailer $logMailer,
        ?SesMailer $sesMailer = null,
        ?SendGridMailer $sendGridMailer = null,
        ?MailgunMailer $mailgunMailer = null
    ) {
        $this->smtpMailer = $smtpMailer;
        $this->logMailer = $logMailer;
        $this->sesMailer = $sesMailer;
        $this->sendGridMailer = $sendGridMailer;
        $this->mailgunMailer = $mailgunMailer;
    }

    public function create(string $service): EmailSenderInterface
    {
        return match ($service) {
            self::SMTP => $this->smtpMailer,
            self::SES => $this->sesMailer ?? throw new InvalidArgumentException('SES service not configured'),
            self::SENDGRID => $this->sendGridMailer ?? throw new InvalidArgumentException('SendGrid service not configured'),
            self::MAILGUN => $this->mailgunMailer ?? throw new InvalidArgumentException('Mailgun service not configured'),
            self::LOG => $this->logMailer,
            default => throw new InvalidArgumentException("Unknown email service: {$service}")
        };
    }

    public function getAvailableServices(): array
    {
        $services = [self::SMTP, self::LOG];
        
        if ($this->sesMailer !== null) {
            $services[] = self::SES;
        }
        
        if ($this->sendGridMailer !== null) {
            $services[] = self::SENDGRID;
        }
        
        if ($this->mailgunMailer !== null) {
            $services[] = self::MAILGUN;
        }
        
        return $services;
    }
} 