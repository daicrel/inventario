<?php

// src/Application/Product/Handler/CreateProductHandler.php

namespace App\Application\Product\Handler;

use App\Application\Product\Command\CreateProductCommand;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\Variant;
use App\Domain\Product\Event\ProductCreatedDomainEvent;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Product\ValueObject\ProductDescription;
use App\Domain\Product\ValueObject\ProductId;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\VariantId;
use App\Domain\Product\ValueObject\Price;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class CreateProductHandler
{
    private ProductRepository $productRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ProductRepository $productRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->productRepository = $productRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(CreateProductCommand $command): void
    {

        // Validación: comprobar si ya existe un producto con el mismo nombre
        if ($this->productRepository->findByName($command->getProductName())) {
            throw new \DomainException('Ya existe un producto con ese nombre.');
        }

        $productId = new ProductId(Uuid::uuid4()->toString());
        $productName = new ProductName($command->getProductName());
        $productDescription = new ProductDescription($command->getProductDescription());
        $productPrice = new Price($command->getPrice());

        $product = new Product(
            $productId,
            $productName,
            $productDescription,
            $productPrice,
            $command->getStock()
        );

        foreach ($command->getVariants() as $variantData) {
            $variantId = new VariantId(Uuid::uuid4()->toString());
            $variantName = new ProductName($variantData['name']);
            $variantPrice = new Price($variantData['price']);
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

        // Guardar el producto
        $this->productRepository->save($product);

        // Despachar evento de dominio para activar el listener de envío de correo
        $this->eventDispatcher->dispatch(
            new ProductCreatedDomainEvent(
                $product,
                (string)$productId,
                (string)$productName,
                (string)$productDescription,
                $productPrice->value(),
                $command->getStock()
            )
        );
    }
}
