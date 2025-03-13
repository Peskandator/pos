<?php

namespace App\Order\Action;

use App\Entity\Company;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Order\Requests\CreateOrderRequest;
use App\Order\Services\OrderItemHelper;
use App\Product\ORM\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class EditOrderAction
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
        private readonly OrderItemHelper $orderItemHelper,
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
            if (array_key_exists($item->getId(), $mergedOrderItems)) {
                $currentUpdatedItems[] = $item->getId();
                $item->setQuantity($mergedOrderItems[$item->getId()]);
                continue;
            }

            $this->entityManager->remove($item);
        }
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        foreach ($mergedOrderItems as $productId => $quantity) {
            if (!array_key_exists($productId, $currentUpdatedItems)) {
                $product = $this->productRepository->find($productId);
                $item = new OrderItem($order, $product, $quantity);

                $order->addOrderItem($item);
                $this->entityManager->persist($item);
            }
        }

        $this->entityManager->flush();
    }
}
