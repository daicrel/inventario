<?php

// src/Application/Product/Query/GetProductByIdQuery.php

namespace App\Application\Product\Query;

class GetProductByIdQuery
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