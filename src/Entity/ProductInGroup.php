<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="product_in_group")
 */
class ProductInGroup
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
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productsInGroup")
     * @ORM\JoinColumn(nullable=false)
     */
    private Product $group;
    /**
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private int $quantity;

    public function __construct(
        Product $group,
        Product $product,
        int $quantity,
    ) {
        $this->product = $product;
        $this->group = $group;
        $this->quantity = $quantity;
    }


    public function getId(): int
    {
        return $this->id;
    }

   public function getProduct(): Product
   {
       return $this->product;
   }

   public function getGroup(): Product
   {
       return $this->group;
   }

   public function getQuantity(): int
   {
       return $this->quantity;
   }
}
