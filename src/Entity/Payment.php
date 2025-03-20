<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="payment")
 */
class Payment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="payments")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
     */
    private Order $order;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, nullable=false)
     */
    private float $amount;

    /**
     * @ORM\Column(name="payment_time", type="datetime", nullable=false)
     */
    private \DateTimeInterface $paymentTime;

    /**
     * @ORM\Column(name="payment_method", type="string", length=50, nullable=false)
     */
    private string $paymentMethod;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderItemPayment", mappedBy="payment", cascade={"persist", "remove"})
     */
    private Collection $orderItemPayments;

    public function __construct()
    {
        $this->orderItemPayments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getPaymentTime(): \DateTimeInterface
    {
        return $this->paymentTime;
    }

    public function setPaymentTime(\DateTimeInterface $paymentTime): self
    {
        $this->paymentTime = $paymentTime;
        return $this;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getOrderItemPayments(): Collection
    {
        return $this->orderItemPayments;
    }
}
