<?php

// src/Application/Product/EventListener/ProductCreatedListener.php

namespace App\Application\Product\EventListener;

use App\Domain\Product\Event\ProductCreatedDomainEvent;
use App\Domain\Notification\EmailSenderInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ProductCreatedDomainEvent::class)]
class ProductCreatedListener
{
    public function __construct(private EmailSenderInterface $emailSender) {}

    public function __invoke(ProductCreatedDomainEvent $event): void
    {
        $product = $event->product();

        $subject = 'Nuevo producto creado';
        $body = sprintf(
            "Se ha creado un nuevo producto:\n\nNombre: %s\nDescripción: %s\nPrecio: %.2f",
            $event->productName,
            $event->productDescription,
            $event->productPrice
        );

        $this->emailSender->send('daicrel@gmail.com', $subject, $body);
    }
}
