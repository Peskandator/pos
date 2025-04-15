<?php

namespace App\Entity;

use App\Order\Requests\CreateOrderRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;
    /**
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private ?string $description;
    /**
     * @ORM\Column(name="inventory_number", type="integer", length=30, nullable=false)
     */
    private ?int $inventoryNumber;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DiningTable")
     * @ORM\JoinColumn(name="dining_table_id", referencedColumnName="id", nullable=true)
     */
    private ?DiningTable $diningTable;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="orders")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     */
    private Company $company;
    /**
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order")
     */
    private Collection $orderItems;
    /**
     * @ORM\Column(name="creation_date", type="date", nullable=false)
     */
    private \DateTimeInterface $creationDate;
    /**
     * @ORM\Column(name="update_date", type="date", nullable=false)
     */
    private \DateTimeInterface $updateDate;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Payment", mappedBy="order")
     */
    private Collection $payments;


    public function __construct(
        Company $company,
        CreateOrderRequest $createOrderRequest,
    ){
        $this->company = $company;
        $this->diningTable = $createOrderRequest->diningTable;
        $this->description = $createOrderRequest->description;
        $this->inventoryNumber = $createOrderRequest->inventoryNumber;
        $this->orderItems = new ArrayCollection();
        $this->creationDate = new \DateTimeImmutable();
        $this->updateDate = new \DateTimeImmutable();
        $this->payments = new ArrayCollection();
    }

    public function updateFromRequest(CreateOrderRequest $request): void
    {
        $this->description = $request->description;
        $this->updateDate = new \DateTimeImmutable();
        $this->diningTable = $request->diningTable;
        $this->inventoryNumber = $request->inventoryNumber;
    }

    public function update(): void
    {
        $this->updateDate = new \DateTimeImmutable();
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDiningTable(): ?DiningTable
    {
        return $this->diningTable;
    }

    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): void
    {
        $orderItems = $this->getOrderItems();
        $orderItems->add($orderItem);
    }

    public function getOrderItemsText(int $length): string
    {
        $text = '';
        $orderItems = $this->getOrderItems();

        $itemsCount = $orderItems->count();
        $counter = 0;

        /** @var OrderItem $orderItem */
        foreach ($orderItems as $orderItem) {
            $counter++;
            $text .= $orderItem->getQuantity() . 'x ' . $orderItem->getProductName();
            if ($counter < $itemsCount) {
                $text .= ', ';
            }
        }

        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length - 3) . '...';
        }

        return $text;
    }

    public function clearOrderItems(): void
    {
        $this->orderItems = new ArrayCollection();
//        $this->orderItems->clear(); // TODO: WHY IS NOT WORKING???????
    }

    public function getCreationDate(): \DateTimeInterface
    {
        return $this->creationDate;
    }

    public function getUpdateDate(): \DateTimeInterface
    {
        return $this->updateDate;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getInventoryNumber(): ?int
    {
        return $this->inventoryNumber;
    }

    public function setInventoryNumber(?int $inventoryNumber): void
    {
        $this->inventoryNumber = $inventoryNumber;
    }

    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function getTotalPrice(): float
    {
        $totalAmount = 0;

        foreach ($this->getOrderItems() as $item) {
            $totalAmount += $item->getQuantity() * $item->getPrice();
        }

        return $totalAmount;
    }

    public function getTotalPaidAmount(): float
    {
        $totalPaidAmount = 0;

        /** @var OrderItem $item */
        foreach ($this->getOrderItems() as $item) {
            $totalPaidAmount += $item->getPaidAmount();
        }

        return $totalPaidAmount;
    }

    public function getRemainingAmountToPay(): float
    {
        return $this->getTotalPrice() - $this->getTotalPaidAmount();
    }
}
