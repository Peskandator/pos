<?php

namespace App\Order\Action;

use App\Entity\Company;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Order\Requests\CreateOrderRequest;
use App\Order\Services\OrderItemHelper;
use Doctrine\ORM\EntityManagerInterface;

class EditOrderAction
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderItemHelper $orderItemHelper,
        private readonly CreateOrderItemAction $createOrderItemAction,
    ) {
    }

    public function __invoke(Company $company, Order $order, CreateOrderRequest $request, array $orderItems): void
    {
        $order->updateFromRequest($request);

        $mergedOrderItems = $this->orderItemHelper->mergeDuplicateOrderItems($orderItems);

        $currentUpdatedItems = [];
        $currentItems = $order->getOrderItems();

        /**
         * @var OrderItem $item
         */
        foreach ($currentItems as $item) {
            $productId = $item->getProduct()->getId();
            if (array_key_exists($productId, $mergedOrderItems)) {
                $currentUpdatedItems[] = $productId;
                $item->setQuantity($mergedOrderItems[$productId]);
                continue;
            }

            $this->entityManager->remove($item);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        foreach ($mergedOrderItems as $productId => $quantity) {
            if (!in_array($productId, $currentUpdatedItems, true)) {
                $this->createOrderItemAction->create($order, $productId, $quantity);
            }
        }

        $this->entityManager->flush();
    }
}
