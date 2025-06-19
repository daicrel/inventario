<?php

// src/Domain/Product/Entity/Product.php

namespace App\Domain\Product\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Product\ValueObject\ProductId;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\ProductDescription;
use App\Domain\Product\ValueObject\Price;

#[ORM\Entity]
#[ORM\Table(name: "product")]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: "string", name: "id", length: 36)]
    private string $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "float")]
    private float $price;

    #[ORM\Column(type: "integer")]
    private int $stock;

    #[ORM\OneToMany(
        mappedBy: "product",
        targetEntity: Variant::class,
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    private Collection $variants;

    public function __construct(
        ProductId $id,
        ProductName $name,
        ProductDescription $description,
        Price $price,
        int $stock
    ) {
        if ($price->value() < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo');
        }

        if ($stock < 0) {
            throw new \InvalidArgumentException('El stock no puede ser negativo');
        }

        $this->id = (string)$id;
        $this->name = (string)$name;
        $this->description = (string)$description;
        $this->price = $price->value();
        $this->stock = $stock;
        $this->variants = new ArrayCollection();
    }

    public function addVariant(Variant $variant): void
    {
        $this->variants[] = $variant;
    }

    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function clearVariants(): void
    {
        $this->variants->clear();
    }

    public function getId(): ProductId
    {
        return new ProductId($this->id);
    }

    public function getName(): ProductName
    {
        return new ProductName($this->name);
    }

    public function getDescription(): ProductDescription
    {
        return new ProductDescription($this->description);
    }

    public function price(): Price
    {
        return new Price($this->price);
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getNameValue(): string
    {
        return $this->name;
    }

    public function updateName(ProductName $name): void
    {
        $this->name = (string)$name;
    }

    public function updateDescription(ProductDescription $description): void
    {
        $this->description = (string)$description;
    }

    public function updatePrice(float $price): void
    {
        if ($price < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo');
        }
        $this->price = $price;
    }

    public function updateStock(int $stock): void
    {
        if ($stock < 0) {
            throw new \InvalidArgumentException('El stock no puede ser negativo');
        }
        $this->stock = $stock;
    }
}
