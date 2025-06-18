<?php

// src/Application/Product/Handler/DeleteProductHandler.php

namespace App\Application\Product\Handler;

use App\Application\Product\Command\DeleteProductCommand;
use App\Domain\Product\Repository\ProductRepository;

final class DeleteProductHandler
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function __invoke(DeleteProductCommand $command): void
    {
        $product = $this->productRepository->findById($command->getProductId());
        if (!$product) {
            throw new \DomainException('Producto no encontrado.');
        }
        $this->productRepository->delete($product);
    }
}
