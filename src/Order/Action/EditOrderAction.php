<?php

namespace App\Order\Action;

use App\Entity\Company;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Order\Requests\CreateOrderRequest;
use App\Product\ORM\ProductRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class EditOrderAction
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function __invoke(Company $company, Order $order, CreateOrderRequest $request, array $orderItems): void
    {
        $order->updateFromRequest($request);

        $currentItems = $order->getOrderItems();
        $order->clearOrderItems();

        foreach ($currentItems as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        foreach ($orderItems as $orderItem) {
            $product = $this->productRepository->find($orderItem['product']);
            $item = new OrderItem($order, $product, $orderItem['quantity']);

            $order->addOrderItem($item);
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }
}
