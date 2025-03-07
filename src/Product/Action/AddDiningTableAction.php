<?php

namespace App\Product\Action;

use App\Entity\Company;
use App\Entity\DiningTable;
use Doctrine\ORM\EntityManagerInterface;

class AddDiningTableAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Company $company, int $number, ?string $description): void
    {
        $table = new DiningTable(
            $company,
            $number,
            $description ?? '',
        );

        $this->entityManager->persist($table);
        $company->getAllDiningTables()->add($table);

        $this->entityManager->flush();
    }
}
