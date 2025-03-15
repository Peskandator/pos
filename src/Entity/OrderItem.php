<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="order_item")
 */
class OrderItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;
    /**
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private int $quantity;
    /**
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    private Product $product;
    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="orderItems")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
     */
    private Order $order;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderItemPayment", mappedBy="orderItem", cascade={"persist", "remove"})
     */
    private Collection $orderItemPayments;
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isPaid = false;

    public function __construct(
        Order $order,
        Product $product,
        int $quantity
    ){
        $this->order = $order;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->orderItemPayments = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function isPaid(): bool
    {
        return $this->isPaid;
    }

    public function markAsPaid(): void
    {
        $this->isPaid = true;
    }

    public function getOrderItemPayments(): Collection
    {
        return $this->orderItemPayments;
    }

    public function addOrderItemPayment(OrderItemPayment $orderItemPayment): self
    {
        if (!$this->orderItemPayments->contains($orderItemPayment)) {
            $this->orderItemPayments->add($orderItemPayment);
            $orderItemPayment->setOrderItem($this);
        }
        return $this;
    }

    public function removeOrderItemPayment(OrderItemPayment $orderItemPayment): self
    {
        if ($this->orderItemPayments->removeElement($orderItemPayment)) {
            if ($orderItemPayment->getOrderItem() === $this) {
                $orderItemPayment->setOrderItem(null);
            }
        }
        return $this;
    }

    public function getPrice(): float
    {
        return $this->product->getPrice();
    }

    public function getPriceIncludingVat(): float
    {
        $price = $this->getPrice();
        $vatRate = $this->product->getVatRate();

        return $price + ($price * ($vatRate / 100));
    }

    public function getProductName(): string
    {
        return $this->product->getName();
    }
}
