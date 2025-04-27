<?php

namespace App\Product\Action;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderItemPayment;
use App\Entity\Payment;
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
        $order->clearPayments();

        /** @var OrderItem $orderItem */
        foreach ($order->getOrderItems() as $orderItem) {
            /** @var OrderItemPayment $orderItemPayment */
            foreach ($orderItem->getOrderItemPayments() as $orderItemPayment) {
                $this->entityManager->remove($orderItemPayment);
            }
            $this->entityManager->remove($orderItem);
        }

        /** @var Payment $payment */
        foreach ($order->getPayments() as $payment) {
            $this->entityManager->remove($payment);
        }

        $this->entityManager->remove($order);
        $this->entityManager->flush();
    }
}
