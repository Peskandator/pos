<?php

namespace App\Product\Requests;

use App\Entity\Category;

class CreateProductRequest
{
    public function __construct(
        public string $name,
        public int $inventoryNumber,
        public ?string $manufacturer,
        public ?Category $category,
        public bool $isGroup,
        public ?float $price,
        public ?int $vatRate,
        public ?string $description,
        public ?\DateTimeInterface $updateDate,
    ) {
    }
}
