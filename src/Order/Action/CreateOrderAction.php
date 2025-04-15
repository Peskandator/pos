<?php

namespace App\Order\Action;

use App\Entity\Company;
use App\Entity\Order;
use App\Order\Requests\CreateOrderRequest;
use App\Order\Services\OrderItemHelper;
use Doctrine\ORM\EntityManagerInterface;

class CreateOrderAction
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderItemHelper $orderItemHelper,
        private readonly CreateOrderItemAction $createOrderItemAction,
    ) {
    }

    public function __invoke(Company $company, CreateOrderRequest $request, array $orderItems): void
    {
        $order = new Order(
            $company,
            $request,
        );

        $mergedOrderItems = $this->orderItemHelper->mergeDuplicateOrderItems($orderItems);

        foreach ($mergedOrderItems as $productId => $quantity) {
            $order = $this->createOrderItemAction->create($order, $productId, $quantity);
        }

        $company->getOrders()->add($order);
        $this->entityManager->persist($order);

        $this->entityManager->flush();
    }
}
