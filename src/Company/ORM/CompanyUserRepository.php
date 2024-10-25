<?php

namespace App\Company\ORM;

use App\Entity\CompanyUser;
use Doctrine\ORM\EntityManagerInterface;

class CompanyUserRepository
{
    private CompanyUser|\Doctrine\ORM\EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(CompanyUser::class);
    }

    public function find($id): ?CompanyUser
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
