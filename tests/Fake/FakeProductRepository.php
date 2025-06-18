<?php

// tests/Fake/FakeProductRepository.php

namespace App\Tests\Fake;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;

class FakeProductRepository implements ProductRepository
{
    /** @var Product[] */
    private array $products = [];

    public function save(Product $product): void
    {
        $this->products[] = $product;
    }

    public function findById(string $id): ?Product
    {
        foreach ($this->products as $product) {
            if ((string)$product->getId() === $id) {
                return $product;
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return $this->products;
    }

    public function findByName(string $name): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->getNameValue() === $name) {
                return $product;
            }
        }
        return null;
    }
}
