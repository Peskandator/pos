<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;
    /**
     * @ORM\Column(name="email", type="string", unique=true)
     */
    private string $email;
    /**
     * @ORM\Column(name="password", type="string", nullable=true)
     */
    private ?string $password;
    /**
     * @ORM\Column(name="registration_date", type="datetime", nullable=true)
     */
    private ?DateTimeInterface $registrationDate;
    /**
     * @ORM\Column(name="last_logon", type="datetime", nullable=true)
     */
    private ?DateTimeInterface $lastLogonDate;
    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     */
    private ?string $firstName;
    /**
     * @ORM\Column(name="last_name", type="string", nullable=true)
     */
    private ?string $lastName;
    /**
     * @ORM\OneToMany(targetEntity="CompanyUser", mappedBy="user")
     */
    private Collection $companyUsers;


    public function __construct(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
    )
    {
        $this->email = $email;
        $this->password = $password;
        $now = new \DateTimeImmutable();
        $this->registrationDate = $now;
        $this->lastLogonDate = $now;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->companyUsers = new ArrayCollection();
    }

    public function update(
        string $email,
        string $firstName,
        string $lastName
    ): void
    {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function authenticate(string $password, callable $verifyPassword): bool
    {
        return $verifyPassword($password, $this->password);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return implode(
            ' ',
            array_filter(
                [
                    $this->getFirstName(),
                    $this->getLastName()
                ]
            )
        );
    }

    public function setPassword(string $newPassword): void
    {
        $this->password = $newPassword;
    }

    public function getRegistrationDate(): ?DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function lastLogonDate(): ?DateTimeInterface
    {
        return $this->lastLogonDate;
    }

    public function setLastLogonDate(): void
    {
        $this->lastLogonDate = new \DateTimeImmutable();
    }

    public function setRegistrationDate(DateTimeInterface $registrationDate): void
    {
        $this->registrationDate = $registrationDate;
    }

    public function getCompanyUsers(): Collection
    {
        return $this->companyUsers;
    }

    public function isCompanyAdmin(?Company $company): bool
    {
        if (!$company instanceof Company) {
            return false;
        }

        if ($this->getCompanyUser($company)) {
            return $this->getCompanyUser($company)->isAdmin();
        }

        return false;
    }

    public function getCompanyUser(Company $company): ?CompanyUser
    {
        $companyUsers = $this->getCompanyUsers();
        /**
         * @var CompanyUser $companyUser
         */
        foreach ($companyUsers as $companyUser) {
            if ($companyUser->getCompany()->getId() === $company->getId()) {
                return $companyUser;
            }
        }

        return null;
    }

    public function isCompanyUser(Company $company): bool
    {
        $companyUser = $this->getCompanyUser($company);
        return $companyUser !== null;
    }
}
