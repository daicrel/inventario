<?php

// src/Infrastructure/Product/Repository/DoctrineProductRepository.php 

namespace App\Infrastructure\Product\Repository;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineProductRepository implements ProductRepository
{
    private EntityManagerInterface $em;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(Product::class);
    }

    public function save(Product $product): void
    {
        $this->em->persist($product);
        $this->em->flush();
    }

    public function findById(string $id): ?Product
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->em->createQueryBuilder()
            ->select('p', 'v')
            ->from(Product::class, 'p')
            ->leftJoin('p.variants', 'v')
            ->getQuery()
            ->getResult();
    }

    public function findByName(string $name): ?Product
    {
        return $this->repository->findOneBy(['name' => $name]);
    }
}
