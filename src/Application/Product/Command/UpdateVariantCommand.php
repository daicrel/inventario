<?php

// src/Application/Product/Command/UpdateVariantCommand.php

namespace App\Application\Product\Command;

class UpdateVariantCommand
{
    private string $productId;
    private string $variantId;
    private ?string $name;
    private ?float $price;
    private ?int $stock;
    private ?string $image;

    public function __construct(
        string $productId,
        string $variantId,
        ?string $name = null,
        ?float $price = null,
        ?int $stock = null,
        ?string $image = null
    ) {
        $this->productId = $productId;
        $this->variantId = $variantId;
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
        $this->image = $image;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getVariantId(): string
    {
        return $this->variantId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }
} 