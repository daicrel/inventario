<?php

// src/Application/Product/Query/Handler/GetProductByIdHandler.php

namespace App\Application\Product\Query\Handler;

use App\Application\Product\Query\GetProductByIdQuery;
use App\Application\Product\Query\Response\ProductResponse;
use App\Domain\Product\Repository\ProductRepository;

class GetProductByIdHandler
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function __invoke(GetProductByIdQuery $query): ?ProductResponse
    {
        $product = $this->productRepository->findById($query->getProductId());
        
        if (!$product) {
            return null;
        }
        
        return ProductResponse::fromDomain($product);
    }
} 