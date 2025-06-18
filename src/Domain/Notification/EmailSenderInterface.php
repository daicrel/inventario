<?php

// src/Domain/Notification/EmailSenderInterface.php

namespace App\Domain\Notification;

interface EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void;
}
