<?php

namespace App\Product\Action;

use App\Entity\Company;
use App\Entity\Product;
use App\Entity\ProductInGroup;
use App\Product\ORM\ProductRepository;
use App\Product\Requests\CreateProductRequest;
use Doctrine\ORM\EntityManagerInterface;

class AddProductAction
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function __invoke(Company $company, CreateProductRequest $request, array $productsInGroup): void
    {
        $product = new Product
        (
            $company,
            $request,
        );

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

        $this->entityManager->persist($product);
        $company->getAllProducts()->add($product);

        $this->entityManager->flush();
    }
}
