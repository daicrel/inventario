<?php

// src/Application/Product/Command/DeleteProductCommand.php

namespace App\Application\Product\Command;

class DeleteProductCommand
{
    private string $productId;

    public function __construct(string $productId)
    {
        $this->productId = $productId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }
}
