<?php

namespace App\Company\ORM;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;

class CompanyRepository
{
    private Company|\Doctrine\ORM\EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Company::class);
    }

    public function find($id): ?Company
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
