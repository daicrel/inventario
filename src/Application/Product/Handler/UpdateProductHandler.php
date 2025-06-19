<?php
// src/Application/Product/Handler/UpdateProductHandler.php

namespace App\Application\Product\Handler;

use App\Application\Product\Command\UpdateProductCommand;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\ProductDescription;
use App\Domain\Product\Entity\Variant;
use App\Domain\Product\ValueObject\VariantId;
use Ramsey\Uuid\Uuid;

final class UpdateProductHandler
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function __invoke(UpdateProductCommand $command): void
    {
        $product = $this->productRepository->findById($command->getProductId());
        if (!$product) {
            throw new \DomainException('Producto no encontrado.');
        }

        // Validar que el nuevo nombre no exista en otro producto
        if ($command->getProductName() !== null && $command->getProductName() !== $product->getNameValue()) {
            $existingProduct = $this->productRepository->findByName($command->getProductName());
            if ($existingProduct && (string)$existingProduct->getId() !== $command->getProductId()) {
                throw new \DomainException('Ya existe un producto con ese nombre.');
            }
        }

        // Actualizar campos si están presentes
        if ($command->getProductName() !== null) {
            $product->updateName(new ProductName($command->getProductName()));
        }

        if ($command->getProductDescription() !== null) {
            $product->updateDescription(new ProductDescription($command->getProductDescription()));
        }

        if ($command->getPrice() !== null) {
            $product->updatePrice($command->getPrice());
        }

        if ($command->getStock() !== null) {
            $product->updateStock($command->getStock());
        }

        // Actualizar variantes si están presentes
        if ($command->getVariants() !== null) {
            $product->clearVariants();
            foreach ($command->getVariants() as $variantData) {
                $variantId = new VariantId(Uuid::uuid4()->toString());
                $variantName = new ProductName($variantData['name']);
                $variantPrice = new \App\Domain\Product\ValueObject\Price($variantData['price']);
                $variantStock = $variantData['stock'];
                $variantImage = $variantData['image'] ?? null;

                $variant = new Variant(
                    $variantId,
                    $product,
                    $variantName,
                    $variantPrice,
                    $variantStock,
                    $variantImage
                );

                $product->addVariant($variant);
            }
        }

        $this->productRepository->save($product);
    }
}
