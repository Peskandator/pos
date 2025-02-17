<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="dining_table")
 */
class Table
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;
    /**
     * @ORM\Column(name="description", type="string", length=100, nullable=true)
     */
    private ?string $description;
    /**
     * @ORM\Column(name="number", type="integer", nullable=false)
     */
    private int $number;
    /**
     * @ORM\Column(name="is_deleted", type="boolean", nullable=false)
     */
    private bool $isDeleted;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="tables")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     */
    private Company $company;

    public function __construct(
        Company $company,
        int $number,
        string $description,
    ){
        $this->company = $company;
        $this->number = $number;
        $this->description = $description;
        $this->isDeleted = false;
    }

    public function update(int $number, string $name)
    {
        $this->number = $number;
        $this->description = $name;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getDescription(): string
    {
        return $this->description;
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
