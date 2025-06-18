<?php

// src/Domain/Product/Entity/Variant.php

namespace App\Domain\Product\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\Product\ValueObject\ProductId;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\VariantId;
use InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: "variant")]
class Variant
{
    #[ORM\Id]
    #[ORM\Column(type: "string", name: "variant_id")]
    private string $variantId;

    #[ORM\ManyToOne(
        targetEntity: Product::class,
        inversedBy: "variants"
    )]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Product $product;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "float")]
    private float $price;

    #[ORM\Column(type: "integer")]
    private int $stock;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $image;

    public function __construct(
        VariantId $variantId,
        Product $product,
        ProductName $name,
        float $price,
        int $stock,
        ?string $image = null
    ) {
        if ($price < 0) {
            throw new InvalidArgumentException("El precio no puede ser negativo.");
        }

        if ($stock < 0) {
            throw new InvalidArgumentException("El stock no puede ser negativo.");
        }

        $this->variantId = $variantId->value(); // Guardamos el valor string en la BD
        $this->product = $product;
        $this->name = (string) $name;
        $this->price = $price;
        $this->stock = $stock;
        $this->image = $image;
    }

    public function getId(): VariantId
    {
        return new VariantId($this->variantId);
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getProductId(): ProductId
    {
        return new ProductId($this->product->getId());
    }

    public function getName(): ProductName
    {
        return new ProductName($this->name);
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function changePrice(float $newPrice): void
    {
        if ($newPrice < 0) {
            throw new InvalidArgumentException("El precio no puede ser negativo.");
        }
        $this->price = $newPrice;
    }

    public function changeStock(int $newStock): void
    {
        if ($newStock < 0) {
            throw new InvalidArgumentException("El stock no puede ser negativo.");
        }
        $this->stock = $newStock;
    }

    public function changeImage(?string $newImage): void
    {
        $this->image = $newImage;
    }
}
