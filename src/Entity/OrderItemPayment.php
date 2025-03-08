<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="order_item_payment")
 */
class OrderItemPayment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrderItem", inversedBy="orderItemPayments")
     * @ORM\JoinColumn(name="order_item_id", referencedColumnName="id", nullable=false)
     */
    private OrderItem $orderItem;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Payment", inversedBy="orderItemPayments")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=false)
     */
    private Payment $payment;

    /**
     * @ORM\Column(name="paid_quantity", type="integer", nullable=false)
     */
    private int $paidQuantity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderItem(): ?OrderItem
    {
        return $this->orderItem;
    }

    public function setOrderItem(OrderItem $orderItem): self
    {
        $this->orderItem = $orderItem;
        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(Payment $payment): self
    {
        $this->payment = $payment;
        return $this;
    }

    public function getPaidQuantity(): int
    {
        return $this->paidQuantity;
    }

    public function setPaidQuantity(int $paidQuantity): self
    {
        $this->paidQuantity = $paidQuantity;
        return $this;
    }
}
