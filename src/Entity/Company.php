<?php

namespace App\Entity;

use App\Company\Requests\CreateCompanyRequest;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="company")
 */
class Company
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;
    /**
     * @ORM\Column(name="name", type="string")
     */
    private string $name;
    /**
     * @ORM\Column(name="street", type="string", nullable=true)
     */
    private ?string $street;
    /**
     * @ORM\Column(name="city", type="string", nullable=true)
     */
    private ?string $city;
    /**
     * @ORM\Column(name="country", type="string", nullable=true)
     */
    private ?string $country;
    /**
     * @ORM\Column(name="zip_code", type="string", nullable=true)
     */
    private ?string $zipCode;
    /**
     * @ORM\Column(name="company_id", type="string", nullable=true)
     */
    private ?string $companyId;
    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=true)
     */
    private ?DateTimeInterface $creationDate;
    /**
     * @ORM\OneToMany(targetEntity="CompanyUser", mappedBy="company")
     */
    private Collection $companyUsers;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Category", mappedBy="company")
     */
    private Collection $categories;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DiningTable", mappedBy="company")
     */
    private Collection $diningTables;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="company")
     */
    private Collection $products;
    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="company")
     */
    private Collection $orders;


    public function __construct(
        CreateCompanyRequest $request,
    )
    {
        $this->update($request);
        $this->creationDate = new \DateTimeImmutable();
        $this->companyUsers = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->diningTables = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function update(CreateCompanyRequest $request)
    {
        $this->name = $request->name;
        $this->companyId = $request->companyId;
        $this->country = $request->country;
        $this->city = $request->city;
        $this->zipCode = $request->zipCode;
        $this->street = $request->street;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCompanyUsers(): Collection
    {
        return $this->companyUsers;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCompanyId(): ?string
    {
        return $this->companyId;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getAddress(): string
    {
        $country = $this->getCountry();
        $city = $this->getCity();
        $zipCode = $this->getZipCode();
        $street = $this->getStreet();

        return $street . ', ' . $zipCode . ' ' . $city . ', ' . $country;
    }

    public function getCreationDate(): ?DateTimeInterface
    {
        return $this->creationDate;
    }

    public function isCompanyUser(User $user): bool
    {
        $entityUser = $user->getCompanyUser($this);
        if ($entityUser !== null) {
            return true;
        }

        return false;
    }

    public function getAllCategories(): Collection
    {
        return $this->categories;
    }

    public function getAllDiningTables(): Collection
    {
        return $this->diningTables;
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function getCategories(): array
    {
        $notDeletedCategories = [];
        $categories = $this->categories;
        /**
         * @var Category $category
         */
        foreach ($categories as $category) {
            if (!$category->isDeleted()) {
                $notDeletedCategories[] = $category;
            }
        }

        return $notDeletedCategories;
    }

    public function getDiningTables(): array
    {
        $notDeletedTables = [];
        $tables = $this->diningTables;
        /**
         * @var DiningTable $table
         */
        foreach ($tables as $table) {
            if (!$table->isDeleted()) {
                $notDeletedTables[] = $table;
            }
        }

        return $notDeletedTables;
    }

    public function getAllProducts(): Collection
    {
        return $this->products;
    }

    public function getProducts(): array
    {
        $notDeletedProducts = [];
        $products = $this->products;
        /**
         * @var Product $product
         */
        foreach ($products as $product) {
            if (!$product->isDeleted()) {
                $notDeletedProducts[] = $product;
            }
        }

        return $notDeletedProducts;
    }

    public function getSingleProducts(): array
    {
        $singleProducts = [];
        $notDeletedProduts = $this->getProducts();
        /**
         * @var Product $product
         */
        foreach ($notDeletedProduts as $product) {
            if (!$product->isGroup()) {
                $singleProducts[] = $product;
            }
        }

        return $singleProducts;

    }

    public function getProductGroups(): array
    {
        $productGroups = [];
        $notDeletedProduts = $this->getProducts();
        /**
         * @var Product $product
         */
        foreach ($notDeletedProduts as $product) {
            if ($product->isGroup()) {
                $productGroups[] = $product;
            }
        }

        return $productGroups;
    }
}
