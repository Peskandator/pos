<?php

namespace App\Product\Action;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class DeleteProductAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Product $product): void
    {
        $product->setDeleted(true);
        $this->entityManager->flush();
    }
}
