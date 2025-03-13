<?php

namespace App\Order\Action;

use App\Entity\Company;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Order\Requests\CreateOrderRequest;
use App\Order\Services\OrderItemHelper;
use App\Product\ORM\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class CreateOrderAction
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
        private readonly OrderItemHelper $orderItemHelper,
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
            $product = $this->productRepository->find($productId);
            $item = new OrderItem($order, $product, $quantity);

            $order->addOrderItem($item);
            $this->entityManager->persist($item);
        }

        $company->getOrders()->add($order);
        $this->entityManager->persist($order);

        $this->entityManager->flush();
    }
}
