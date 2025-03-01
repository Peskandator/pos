<?php

namespace App\Product\ORM;

use App\Entity\DiningTable;
use Doctrine\ORM\EntityManagerInterface;

class DiningTableRepository
{
    private DiningTable|\Doctrine\ORM\EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(DiningTable::class);
    }

    public function find($id): ?DiningTable
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
