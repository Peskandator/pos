<?php

namespace App\Order\Requests;

use App\Entity\DiningTable;

class CreateOrderRequest
{
    public function __construct(
        public DiningTable $diningTable,
        public ?string $description,
        public ?int $inventoryNumber,
    ) {
    }
}
