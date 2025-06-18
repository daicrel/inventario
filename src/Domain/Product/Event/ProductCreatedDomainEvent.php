<?php

// src/Domain/Product/Event/ProductCreatedDomainEvent.php

namespace App\Domain\Product\Event;

use App\Domain\Product\Entity\Product;

final class ProductCreatedDomainEvent
{
    public function __construct(
        private Product $product,
        public string $productId,
        public string $productName,
        public string $productDescription,
        public float $productPrice
    ) {}

    public function product(): Product
    {
        return $this->product;
    }
}
