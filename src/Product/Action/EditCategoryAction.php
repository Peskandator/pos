<?php

namespace App\Product\Action;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

class EditCategoryAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Category $category, int $code, string $name): void
    {
        $category->update($code, $name);
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }
}
