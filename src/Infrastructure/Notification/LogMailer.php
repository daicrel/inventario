<?php

// src/Infrastructure/Notification/LogMailer.php

namespace App\Infrastructure\Notification;

use Psr\Log\LoggerInterface;

class LogMailer extends AbstractEmailSender
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct('daicrela@gmail.com', $logger);
    }

    protected function doSend(string $to, string $subject, string $body): void
    {
        $this->logger->info('Email would be sent', [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'provider' => 'LogMailer (development)'
        ]);

        // En desarrollo, también podemos imprimir en la consola
        if (php_sapi_name() === 'cli') {
            echo "📧 EMAIL (DEV MODE):\n";
            echo "To: {$to}\n";
            echo "Subject: {$subject}\n";
            echo "Body: {$body}\n";
            echo "---\n";
        }
    }
} 