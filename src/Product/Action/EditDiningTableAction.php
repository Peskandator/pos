<?php

namespace App\Product\Action;

use App\Entity\DiningTable;
use Doctrine\ORM\EntityManagerInterface;

class EditDiningTableAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(DiningTable $table, int $number, ?string $description): void
    {
        $table->update($number, $description);
        $this->entityManager->persist($table);
        $this->entityManager->flush();
    }
}
