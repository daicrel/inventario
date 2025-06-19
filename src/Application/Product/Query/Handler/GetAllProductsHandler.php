<?php

// src/Application/Product/Query/Handler/GetAllProductsHandler.php

namespace App\Application\Product\Query\Handler;

use App\Application\Product\Query\GetAllProductsQuery;
use App\Application\Product\Query\Response\ProductResponse;
use App\Domain\Product\Repository\ProductRepository;

class GetAllProductsHandler
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @return ProductResponse[]
     */
    public function __invoke(GetAllProductsQuery $query): array
    {
        $products = $this->productRepository->findAll();
        
        return array_map(
            fn($product) => ProductResponse::fromDomain($product),
            $products
        );
    }
} 