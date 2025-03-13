<?php

namespace App\Order\Services;

class OrderItemHelper
{
    public function __construct(
    ) {
    }
    public function mergeDuplicateOrderItems(array $orderItems): array
    {
        $uniqueItems = [];

        foreach ($orderItems as $orderItem) {
            if ((int)$orderItem['quantity'] < 1) {
                continue;
            }

            $productId = $orderItem['product'];
            if (array_key_exists($productId, $uniqueItems)) {
                $uniqueItems[$productId] += $orderItem['quantity'];
                continue;
            }
            $uniqueItems[$productId] = $orderItem['quantity'];
        }

        return $uniqueItems;
    }
}