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
     * @ORM\Column(name="name", type="string", length=50)
     */
    private ?string $name;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="categories")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     */
    private Company $company;

    public function __construct(
        Company $company,
        string $name,
    ){
        $this->company = $company;
        $this->name = $name;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
