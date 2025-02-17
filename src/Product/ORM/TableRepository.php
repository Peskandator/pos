<?php

namespace App\Product\ORM;

use App\Entity\Table;
use Doctrine\ORM\EntityManagerInterface;

class TableRepository
{
    private Table|\Doctrine\ORM\EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Table::class);
    }

    public function find($id): ?Table
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
