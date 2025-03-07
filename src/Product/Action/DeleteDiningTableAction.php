<?php

namespace App\Product\Action;

use App\Entity\DiningTable;
use Doctrine\ORM\EntityManagerInterface;

class DeleteDiningTableAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(DiningTable $diningTable): void
    {
        $diningTable->setDeleted(true);
        $this->entityManager->flush();
    }
}
