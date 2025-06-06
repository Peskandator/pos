<?php

namespace App\Product\Action;

use App\Entity\Company;
use App\Entity\Product;
use App\Product\Requests\CreateProductRequest;
use App\Product\Services\ProductInGroupHelper;
use App\Product\Services\ProductsInGroupGenerator;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class EditProductAction
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductsInGroupGenerator $productsInGroupGenerator,
        private readonly ProductInGroupHelper $productInGroupHelper
    ) {
    }

    public function __invoke(Company $company, Product $product, CreateProductRequest $request, array $productsInGroup): void
    {
        if ($request->isGroup) {

            $group = $product->getProductsInGroup();
            $this->deleteCurrentProductsInGroup($group);
            $product->clearProductsInGroup();

            $mergedProductsInGroup = $this->productInGroupHelper->mergeDuplicateProductsInGroup($productsInGroup);
            $this->productsInGroupGenerator->generateProductsInGroup($product, $mergedProductsInGroup);
        }
        $product->updateFromRequest($request);

        $this->entityManager->flush();
    }

    private function deleteCurrentProductsInGroup(Collection $productsInGroup): void
    {
        foreach ($productsInGroup as $productInGroup) {
            $this->entityManager->remove($productInGroup);
        }
    }
}
