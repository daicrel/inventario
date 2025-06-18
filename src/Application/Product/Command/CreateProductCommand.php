<?php

// src/Application/Product/Command/CreateProductCommand.php 

namespace App\Application\Product\Command;

class CreateProductCommand
{

    private string $productId;
    private string $productName;
    private string $productDescription;
    private float $price;
    private int $stock;
    private array $variants;

    public function __construct(
        string $productId,
        string $productName,
        string $productDescription,
        float $price,
        int $stock,
        array $variants = []
    ) {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->productDescription = $productDescription;
        $this->price = $price;
        $this->stock = $stock;
        $this->variants = $variants;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getProductDescription(): string
    {
        return $this->productDescription;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getVariants(): array
    {
        return $this->variants;
    }
}
