<?php

// src/Infrastructure/Notification/AbstractEmailSender.php

namespace App\Infrastructure\Notification;

use App\Domain\Notification\EmailSenderInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractEmailSender implements EmailSenderInterface
{
    protected string $fromEmail;
    protected ?LoggerInterface $logger;

    public function __construct(string $fromEmail = 'daicrela@gmail.com', ?LoggerInterface $logger = null)
    {
        $this->fromEmail = $fromEmail;
        $this->logger = $logger;
    }

    abstract protected function doSend(string $to, string $subject, string $body): void;

    public function send(string $to, string $subject, string $body): void
    {
        // Validación común para todos los servicios
        $this->validateEmail($to);
        $this->validateSubject($subject);
        $this->validateBody($body);

        // Log antes del envío
        $this->logBeforeSend($to, $subject);

        try {
            // Delegar el envío real a la implementación específica
            $this->doSend($to, $subject, $body);
            
            // Log después del envío exitoso
            $this->logAfterSend($to, $subject, true);
        } catch (\Exception $e) {
            // Log de error
            $this->logAfterSend($to, $subject, false, $e->getMessage());
            throw $e;
        }
    }

    protected function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address: {$email}");
        }
    }

    protected function validateSubject(string $subject): void
    {
        if (empty(trim($subject))) {
            throw new \InvalidArgumentException("Subject cannot be empty");
        }

        if (strlen($subject) > 255) {
            throw new \InvalidArgumentException("Subject too long (max 255 characters)");
        }
    }

    protected function validateBody(string $body): void
    {
        if (empty(trim($body))) {
            throw new \InvalidArgumentException("Email body cannot be empty");
        }
    }

    protected function logBeforeSend(string $to, string $subject): void
    {
        if ($this->logger) {
            $this->logger->info('Attempting to send email', [
                'to' => $to,
                'subject' => $subject,
                'provider' => static::class
            ]);
        }
    }

    protected function logAfterSend(string $to, string $subject, bool $success, ?string $errorMessage = null): void
    {
        if ($this->logger) {
            $context = [
                'to' => $to,
                'subject' => $subject,
                'provider' => static::class,
                'success' => $success
            ];

            if ($errorMessage) {
                $context['error'] = $errorMessage;
            }

            if ($success) {
                $this->logger->info('Email sent successfully', $context);
            } else {
                $this->logger->error('Failed to send email', $context);
            }
        }
    }

    protected function getFromEmail(): string
    {
        return $this->fromEmail;
    }
} 