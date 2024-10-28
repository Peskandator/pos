<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="category")
 */
class Category
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
     * @ORM\Column(name="code", type="integer", nullable=false)
     */
    private int $code;
    /**
     * @ORM\Column(name="is_deleted", type="boolean", nullable=false)
     */
    private bool $isDeleted;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="categories")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     */
    private Company $company;

    public function __construct(
        Company $company,
        int $code,
        string $name,
    ){
        $this->company = $company;
        $this->code = $code;
        $this->name = $name;
        $this->isDeleted = false;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setDeleted(bool $isDeleted): void
    {
        $this->isDeleted = $isDeleted;
    }
}
