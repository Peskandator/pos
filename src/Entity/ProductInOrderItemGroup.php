<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="product_in_order_item_group")
 */
class ProductInOrderItemGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;
    /**
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product", referencedColumnName="id", nullable=false)
     */
    private Product $product;
    /**
     * @ORM\ManyToOne(targetEntity="OrderItem", inversedBy="productsInGroup")
     * @ORM\JoinColumn(nullable=false)
     */
    private OrderItem $group;
    /**
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private int $quantity;

    public function __construct(
        OrderItem $group,
        ProductInGroup $productInGroup,
    ) {
        $this->group = $group;
        $this->product = $productInGroup->getProduct();
        $this->quantity = $productInGroup->getQuantity();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getGroup(): OrderItem
    {
        return $this->group;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
