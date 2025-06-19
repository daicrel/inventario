<?php

// src/Application/Product/Query/Response/ProductResponse.php

namespace App\Application\Product\Query\Response;

class ProductResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public float $price,
        public int $stock,
        public array $variants = []
    ) {}

    public static function fromDomain(\App\Domain\Product\Entity\Product $product): self
    {
        $variants = [];
        foreach ($product->getVariants() as $variant) {
            $variants[] = [
                'id' => (string) $variant->getId(),
                'name' => (string) $variant->getName(),
                'price' => $variant->getPrice(),
                'stock' => $variant->getStock(),
                'image' => $variant->getImage()
            ];
        }

        return new self(
            (string) $product->getId(),
            (string) $product->getName(),
            (string) $product->getDescription(),
            $product->price()->value(),
            $product->getStock(),
            $variants
        );
    }
} 