<?php

// src/Infrastructure/Notification/MailgunMailer.php

namespace App\Infrastructure\Notification;

use Mailgun\Mailgun;
use Mailgun\Exception\HttpClientException;
use Psr\Log\LoggerInterface;

class MailgunMailer extends AbstractEmailSender
{
    private Mailgun $mailgun;
    private string $domain;

    public function __construct(
        Mailgun $mailgun, 
        string $domain, 
        string $fromEmail = 'daicrela@gmail.com',
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($fromEmail, $logger);
        $this->mailgun = $mailgun;
        $this->domain = $domain;
    }

    protected function doSend(string $to, string $subject, string $body): void
    {
        try {
            $result = $this->mailgun->messages()->send($this->domain, [
                'from' => $this->getFromEmail(),
                'to' => $to,
                'subject' => $subject,
                'text' => $body,
            ]);

            // Verificar si el envío fue exitoso
            if (!$result->getId()) {
                throw new \RuntimeException('Mailgun error: No message ID returned');
            }
        } catch (HttpClientException $e) {
            throw new \RuntimeException('Error sending email via Mailgun: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unexpected error sending email via Mailgun: ' . $e->getMessage(), 0, $e);
        }
    }
} 