<?php

namespace App\Product\Action;

use App\Entity\Company;
use App\Entity\Table;
use Doctrine\ORM\EntityManagerInterface;

class AddTableAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Company $company, int $number, ?string $description): void
    {
        $table = new Table(
            $company,
            $number,
            $description ?? '',
        );

        bdump($table);

        $this->entityManager->persist($table);
        $company->getAllTables()->add($table);

        $this->entityManager->flush();
    }
}
