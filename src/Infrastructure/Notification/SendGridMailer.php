<?php

// src/Infrastructure/Notification/SendGridMailer.php

namespace App\Infrastructure\Notification;

use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TypeException;
use Psr\Log\LoggerInterface;

class SendGridMailer extends AbstractEmailSender
{
    private SendGrid $sendGrid;

    public function __construct(
        SendGrid $sendGrid, 
        string $fromEmail = 'daicrela@gmail.com',
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($fromEmail, $logger);
        $this->sendGrid = $sendGrid;
    }

    protected function doSend(string $to, string $subject, string $body): void
    {
        try {
            $email = new Mail();
            $email->setFrom($this->getFromEmail());
            $email->setSubject($subject);
            $email->addTo($to);
            $email->addContent("text/plain", $body);

            $response = $this->sendGrid->send($email);

            if ($response->statusCode() >= 400) {
                throw new \RuntimeException(
                    'SendGrid error: ' . $response->statusCode() . ' - ' . $response->body()
                );
            }
        } catch (TypeException $e) {
            throw new \RuntimeException('Error creating SendGrid email: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error sending email via SendGrid: ' . $e->getMessage(), 0, $e);
        }
    }
} 