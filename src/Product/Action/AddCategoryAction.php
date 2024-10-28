<?php

namespace App\Product\Action;

use App\Entity\Category;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;

class AddCategoryAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Company $company, int $code, string $name): void
    {
        $category = new Category
        (
            $company,
            $code,
            $name,
        );

        $this->entityManager->persist($category);
        $company->getAllCategories()->add($category);

        $this->entityManager->flush();
    }
}
