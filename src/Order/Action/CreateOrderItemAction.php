<?php

namespace App\Order\Action;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductInOrderItemGroup;
use App\Product\ORM\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class CreateOrderItemAction
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
    ) {
    }


    public function create(Order $order, int $productId, int $quantity): Order
    {
        $product = $this->productRepository->find($productId);
        if (!$product instanceof Product) {
            return $order;
        }

        $item = new OrderItem($order, $product, $quantity);

        if ($product->isGroup()) {
            $productsInItemGroup = new ArrayCollection();

            foreach ($product->getProductsInGroup() as $productInGroup) {
                $productInItemGroup = new ProductInOrderItemGroup($item, $productInGroup);
                $productsInItemGroup->add($productInItemGroup);
                $this->entityManager->persist($productInItemGroup);
            }
            $item->setProductsInGroup($productsInItemGroup);
        }

        $order->addOrderItem($item);
        $this->entityManager->persist($item);

        return $order;
    }
}