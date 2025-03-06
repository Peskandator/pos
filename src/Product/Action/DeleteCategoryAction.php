<?php

namespace App\Product\Action;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

class DeleteCategoryAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Category $category): void
    {
        $category->setDeleted(true);
        $this->entityManager->flush();
    }
}
