<?php

// src/Infrastructure/Notification/SesMailer.php

namespace App\Infrastructure\Notification;

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;
use Psr\Log\LoggerInterface;

class SesMailer extends AbstractEmailSender
{
    private SesClient $sesClient;

    public function __construct(
        SesClient $sesClient, 
        string $fromEmail = 'daicrela@gmail.com',
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($fromEmail, $logger);
        $this->sesClient = $sesClient;
    }

    protected function doSend(string $to, string $subject, string $body): void
    {
        try {
            $result = $this->sesClient->sendEmail([
                'Source' => $this->getFromEmail(),
                'Destination' => [
                    'ToAddresses' => [$to],
                ],
                'Message' => [
                    'Subject' => [
                        'Data' => $subject,
                        'Charset' => 'UTF-8',
                    ],
                    'Body' => [
                        'Text' => [
                            'Data' => $body,
                            'Charset' => 'UTF-8',
                        ],
                    ],
                ],
            ]);

            // Log success if needed
            // error_log("Email sent successfully via SES: " . $result['MessageId']);
        } catch (AwsException $e) {
            throw new \RuntimeException('Error sending email via SES: ' . $e->getMessage(), 0, $e);
        }
    }
} 