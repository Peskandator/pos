<?php

namespace App\Entity;

use App\DancingClub\Enums\CompanyUserRoles;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="company_user")
 */
class CompanyUser
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;
    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="companyUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private Company $company;
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="companyUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;
    /**
     * @ORM\Column(name="roles", type="json")
     */
    private ?array $roles = [];

    public function __construct(
        User $user,
        Company $company,
        array $roles,
    ) {
        $this->user = $user;
        $this->company = $company;
        $this->roles = $roles;
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function isAdmin(): bool
    {
        $roles = $this->roles;
        return in_array(CompanyUserRoles::ADMIN, $roles, true);
    }

    public function isEditor(): bool
    {
        $roles = $this->roles;
        return in_array(CompanyUserRoles::EDTIOR, $roles, true);
    }
}
