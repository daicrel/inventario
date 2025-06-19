<?php

// src/Application/Product/Handler/UpdateVariantHandler.php

namespace App\Application\Product\Handler;

use App\Application\Product\Command\UpdateVariantCommand;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\Price;

final class UpdateVariantHandler
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function __invoke(UpdateVariantCommand $command): void
    {
        $product = $this->productRepository->findById($command->getProductId());
        if (!$product) {
            throw new \DomainException('Producto no encontrado.');
        }

        $variant = null;
        foreach ($product->getVariants() as $productVariant) {
            if ((string)$productVariant->getId() === $command->getVariantId()) {
                $variant = $productVariant;
                break;
            }
        }

        if (!$variant) {
            throw new \DomainException('Variante no encontrada.');
        }

        // Actualizar campos si están presentes
        if ($command->getName() !== null) {
            $variant->changeName(new ProductName($command->getName()));
        }

        if ($command->getPrice() !== null) {
            $variant->changePrice($command->getPrice());
        }

        if ($command->getStock() !== null) {
            $variant->changeStock($command->getStock());
        }

        if ($command->getImage() !== null) {
            $variant->changeImage($command->getImage());
        }

        $this->productRepository->save($product);
    }
} 