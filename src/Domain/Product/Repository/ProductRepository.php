<?php

// src/Domain/Product/Repository/ProductRepository.php 

namespace App\Domain\Product\Repository;

use App\Domain\Product\Entity\Product;

interface ProductRepository
{
    public function save(Product $product): void;
    public function findById(string $id): ?Product;
    public function findAll(): array;
    public function findByName(string $name): ?Product;
    public function delete(Product $product): void;
}
