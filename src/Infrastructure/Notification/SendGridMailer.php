<?php

// src/Infrastructure/Notification/SendGridMailer.php

namespace App\Infrastructure\Notification;

use App\Domain\Notification\EmailSenderInterface;
use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TypeException;

class SendGridMailer implements EmailSenderInterface
{
    private SendGrid $sendGrid;
    private string $fromEmail;

    public function __construct(SendGrid $sendGrid, string $fromEmail = 'daicrela@gmail.com')
    {
        $this->sendGrid = $sendGrid;
        $this->fromEmail = $fromEmail;
    }

    public function send(string $to, string $subject, string $body): void
    {
        try {
            $email = new Mail();
            $email->setFrom($this->fromEmail);
            $email->setSubject($subject);
            $email->addTo($to);
            $email->addContent("text/plain", $body);

            $response = $this->sendGrid->send($email);

            if ($response->statusCode() >= 400) {
                throw new \RuntimeException(
                    'SendGrid error: ' . $response->statusCode() . ' - ' . $response->body()
                );
            }

            // Log success if needed
            // error_log("Email sent successfully via SendGrid");
        } catch (TypeException $e) {
            throw new \RuntimeException('Error creating SendGrid email: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error sending email via SendGrid: ' . $e->getMessage(), 0, $e);
        }
    }
} 