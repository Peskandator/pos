<?php

namespace App\Order\Action;

use App\Entity\Company;
use App\Entity\Order;
use App\Order\Requests\CreateOrderRequest;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class EditOrderAction
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Company $company, Order $order, CreateOrderRequest $request, array $productsInGroup): void
    {
        // TODO


        $this->entityManager->flush();
    }

//    private function deleteCurrentProductsInGroup(Collection $productsInGroup): void
//    {
//        foreach ($productsInGroup as $productInGroup) {
//            $this->entityManager->remove($productInGroup);
//        }
//    }
}
