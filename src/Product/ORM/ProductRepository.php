<?php

namespace App\Product\ORM;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductRepository
{
    private Product|\Doctrine\ORM\EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Product::class);
    }

    public function find($id): ?Product
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
