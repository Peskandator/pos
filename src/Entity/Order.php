<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="order")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\DiningTable")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     */
    private DiningTable $diningTable;
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


    public function __construct(
        Company $company,
        DiningTable $diningTable,
        ?string $description,
    ){
        $this->company = $company;
        $this->diningTable = $diningTable;
        $this->description = $description;
        $this->orderItems = new ArrayCollection();
        $this->creationDate = new \DateTimeImmutable();
        $this->updateDate = new \DateTimeImmutable();
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

    public function getDiningTable(): DiningTable
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
}
