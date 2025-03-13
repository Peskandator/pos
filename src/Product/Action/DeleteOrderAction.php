<?php

namespace App\Product\Action;

use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;

class DeleteOrderAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Order $order): void
    {
        /** @var OrderItem $orderItem */
        foreach ($order->getOrderItems() as $orderItem) {
            $this->entityManager->remove($orderItem);
        }
        $this->entityManager->remove($order);
        $this->entityManager->flush();
    }
}
