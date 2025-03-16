<?php

namespace App\Entity;

use App\Product\Requests\CreateProductRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="product")
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;
    /**
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private string $name;
    /**
     * @ORM\Column(name="inventory_number", type="integer", length=30, nullable=false)
     */
    private ?int $inventoryNumber;
    /**
     * @ORM\Column(name="creation_date", type="date", nullable=false)
     */
    private \DateTimeInterface $creationDate;
    /**
     * @ORM\Column(name="update_date", type="date", nullable=false)
     */
    private \DateTimeInterface $updateDate;
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
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    private bool $isDeleted;
    /**
     * @ORM\Column(name="manufacturer", type="string", nullable=true, length=50)
     */
    private ?string $manufacturer;
    /**
     * @ORM\Column(name="description", type="string", nullable=true, length=50)
     */
    private ?string $description;
    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true)
     */
    private ?Category $category;
    /**
     * @ORM\OneToMany(targetEntity="ProductInGroup", mappedBy="group")
     */
    private Collection $productsInGroup;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="products")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     */
    private Company $company;

    public function __construct(
        Company $company,
        CreateProductRequest $request,
    )
    {
        $this->updateFromRequest($request);
        $this->company = $company;
        $this->isDeleted = false;
        $this->creationDate = new \DateTimeImmutable();
        $this->productsInGroup = new ArrayCollection();
    }

    public function updateFromRequest(CreateProductRequest $request)
    {
        $this->name = $request->name;
        $this->inventoryNumber = $request->inventoryNumber;
        $this->manufacturer = $request->manufacturer;
        $this->category = $request->category;
        $this->isGroup = $request->isGroup;
        $this->price = $request->price;
        $this->vatRate = $request->vatRate;
        $this->description = $request->description;
        $this->updateDate = $request->updateDate;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getInventoryNumber(): int
    {
        return $this->inventoryNumber;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getPriceWithoutVat(): ?float
    {
        $vatRate = $this->getVatRate();
        $price = $this->getPrice();
        if ($vatRate === 0 || $vatRate === null) {
            return $price;
        }

        return $price * ((100 - $vatRate) / 100);
    }

    public function getVatRate(): ?int
    {
        return $this->vatRate;
    }

    public function getVatRatePercentage(): string
    {
        $vatRate = $this->getVatRate();
        if ($vatRate === null) {
            $vatRate = 0;
        }

        return $vatRate . ' %';
    }

    public function getCreationDate(): \DateTimeInterface
    {
        return $this->creationDate;
    }

    public function getUpdateDate(): \DateTimeInterface
    {
        return $this->updateDate;
    }

    public function isGroup(): bool
    {
        return $this->isGroup;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setDeleted(bool $isDeleted): void
    {
        $this->isDeleted = $isDeleted;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getProductsInGroup(): Collection
    {
        return $this->productsInGroup;
    }

    public function clearProductsInGroup(): void
    {
        $this->productsInGroup->clear();
    }
}
