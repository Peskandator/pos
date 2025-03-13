<?php

namespace App\Product\Services;

class ProductInGroupHelper
{
    public function __construct(
    ) {
    }
    public function mergeDuplicateProductsInGroup(array $productsInGroup): array
    {
        $uniqueProducts = [];

        foreach ($productsInGroup as $orderItem) {
            $quantity = (int)$orderItem['quantity'];
            if ($quantity < 1) {
                continue;
            }

            $productId = $orderItem['product'];
            if (array_key_exists($productId, $uniqueProducts)) {
                $uniqueProducts[$productId] += $quantity;
                continue;
            }
            $uniqueProducts[$productId] = $quantity;
        }

        return $uniqueProducts;
    }
}