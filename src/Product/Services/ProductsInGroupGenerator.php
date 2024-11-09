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
        foreach ($productsInGroup as $productInGroupData) {
            $productInGroup = $this->productRepository->find($productInGroupData['product']);
            if ($productInGroup === null) {
                continue;
            }
            $quantity = (int)$productInGroupData['quantity'];
            $productItem = new ProductInGroup($product, $productInGroup, $quantity);
            $this->entityManager->persist($productItem);
            $group->add($productItem);
        }
    }
}