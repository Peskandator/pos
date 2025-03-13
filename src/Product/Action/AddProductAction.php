<?php

namespace App\Product\Action;

use App\Entity\Company;
use App\Entity\Product;
use App\Product\Requests\CreateProductRequest;
use App\Product\Services\ProductInGroupHelper;
use App\Product\Services\ProductsInGroupGenerator;
use Doctrine\ORM\EntityManagerInterface;

class AddProductAction
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductsInGroupGenerator $productsInGroupGenerator,
        private readonly ProductInGroupHelper $productInGroupHelper
    ) {
    }

    public function __invoke(Company $company, CreateProductRequest $request, array $productsInGroup): void
    {
        $product = new Product
        (
            $company,
            $request,
        );

        if ($request->isGroup) {
            $mergedProductsInGroup = $this->productInGroupHelper->mergeDuplicateProductsInGroup($productsInGroup);
            $this->productsInGroupGenerator->generateProductsInGroup($product, $mergedProductsInGroup);
        }

        $company->getAllProducts()->add($product);
        $this->entityManager->persist($product);

        $this->entityManager->flush();
    }
}
