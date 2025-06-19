<?php

// src/Infrastructure/Notification/SesMailer.php

namespace App\Infrastructure\Notification;

use App\Domain\Notification\EmailSenderInterface;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

class SesMailer implements EmailSenderInterface
{
    private SesClient $sesClient;
    private string $fromEmail;

    public function __construct(SesClient $sesClient, string $fromEmail = 'daicrela@gmail.com')
    {
        $this->sesClient = $sesClient;
        $this->fromEmail = $fromEmail;
    }

    public function send(string $to, string $subject, string $body): void
    {
        try {
            $result = $this->sesClient->sendEmail([
                'Source' => $this->fromEmail,
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