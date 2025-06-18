<?php

// src/Infrastructure/Product/Repository/ProductRepository.php 

namespace App\Infrastructure\Product\Repository;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class InMemoryProductRepository implements ProductRepository
{
    private SessionInterface $session;
    private const SESSION_KEY = 'in_memory_products';

    public function __construct(RequestStack $requestStack)
    {
        $session = $requestStack->getSession();
        if (!$session) {
            throw new \RuntimeException('No session available');
        }
        $this->session = $session;
    }

    public function save(Product $product): void
    {
        $products = $this->session->get(self::SESSION_KEY, []);
        $products[(string)$product->getId()] = $product;
        $this->session->set(self::SESSION_KEY, $products);
    }

    public function findById(string $id): ?Product
    {
        $products = $this->session->get(self::SESSION_KEY, []);
        return $products[$id] ?? null;
    }

    public function findAll(): array
    {
        $products = $this->session->get(self::SESSION_KEY, []);
        return array_values($products);
    }

    public function findByName(string $name): ?Product
    {
        $products = $this->session->get(self::SESSION_KEY, []);
        foreach ($products as $product) {
            if (method_exists($product, 'getName') && $product->getName() === $name) {
                return $product;
            }
        }
        return null;
    }
}
