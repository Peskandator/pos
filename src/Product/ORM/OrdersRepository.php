<?php

namespace App\Product\ORM;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

class OrdersRepository
{
    private Order|\Doctrine\ORM\EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Order::class);
    }

    public function find($id): ?Order
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
