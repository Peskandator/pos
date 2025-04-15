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
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private ?float $price;
    /**
     * @ORM\Column(name="vat_rate", type="integer", nullable=true)
     */
    private ?int $vatRate;
    /**
     * @ORM\Column(name="is_group", type="boolean")
     */
    private bool $isGroup;
    /**
     * @ORM\OneToMany(targetEntity="ProductInOrderItemGroup", mappedBy="group", cascade={"persist", "remove"})
     */
    private Collection $productsInGroup;
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

    public function __construct(
        Order $order,
        Product $product,
        int $quantity,
    ){
        $this->order = $order;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->price = $product->getPrice();
        $this->vatRate = $product->getVatRate();
        $this->isGroup = $product->isGroup();

        $this->productsInGroup = new ArrayCollection();
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
        return $this->getPaidQuantity() >= $this->getQuantity();
    }

    public function getPaidQuantity(): int
    {
        $paidQuantity = 0;
        /** @var OrderItemPayment $orderItemPayment */
        foreach ($this->getOrderItemPayments() as $orderItemPayment) {
            $paidQuantity += $orderItemPayment->getPaidQuantity();
        }

        return $paidQuantity;
    }

    public function getRemainingQuantityToPay(): int
    {
        $remainingQuantity = $this->getQuantity() - $this->getPaidQuantity();
        if ($remainingQuantity < 0) {
            return 0;
        }

        return $remainingQuantity;
    }

    public function getTotalPrice(): float
    {
        return $this->getQuantity() * $this->getPrice();
    }

    public function getPaidAmount(): float
    {
        return $this->getPaidQuantity() * $this->getPrice();
    }

    public function getOrderItemPayments(): Collection
    {
        return $this->orderItemPayments;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getProductName(): string
    {
        return $this->product->getName();
    }

    public function isGroup(): bool
    {
        return $this->isGroup;
    }

    public function getProductsInGroup(): Collection
    {
        return $this->productsInGroup;
    }

    public function setProductsInGroup(Collection $productsInGroup): void
    {
        $this->productsInGroup = $productsInGroup;
    }

    public function getPriceWithoutVat(): ?float
    {
        $vatRate = $this->getVatRate();
        $price = $this->getPrice();
        if ($vatRate === 0 || $vatRate === null) {
            return $price;
        }

        return $price / (1 + $vatRate / 100);
    }

    public function getVatRate(): ?int
    {
        return $this->vatRate;
    }
}
