<?php

declare(strict_types=1);

namespace App\Product\Services;

use App\Entity\Product;
use App\Entity\ProductInGroup;
use App\Product\ORM\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductsInGroupGenerator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
    )
    {
    }

    public function generateProductsInGroup(Product $product, array $productsInGroup): void
    {
        $group = $product->getProductsInGroup();
        foreach ($productsInGroup as $productId => $quantity) {
            $productInGroup = $this->productRepository->find($productId);
            if ($productInGroup === null) {
                continue;
            }
            $productItem = new ProductInGroup($product, $productInGroup, $quantity);
            $this->entityManager->persist($productItem);
            $group->add($productItem);
        }
    }
}