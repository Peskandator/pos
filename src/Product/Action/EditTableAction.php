<?php

namespace App\Product\Action;

use App\Entity\Table;
use Doctrine\ORM\EntityManagerInterface;

class EditTableAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Table $table, int $number, ?string $description): void
    {
        $table->update($number, $description);
        $this->entityManager->persist($table);
        $this->entityManager->flush();
    }
}
