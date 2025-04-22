<?php

namespace App\Order\Services;

use App\Entity\Company;
use App\Entity\Order;

class OrdersFilter
{
    public function __construct(
    ) {
    }
    public function getOrdersInRange(Company $company, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $filteredOrders = [];

        /** @var Order $order */
        foreach ($company->getOrders() as $order) {
            $createdAt = $order->getCreationDate();

            if ($createdAt >= $start && $createdAt <= $end) {
                $filteredOrders[] = $order;
            }
        }

        return $filteredOrders;
    }

    // TODO: move here getDateRange method - and refactor in statistics presenter
}