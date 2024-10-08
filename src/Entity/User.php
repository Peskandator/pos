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
     * @ORM\Column(name="roles", type="json")
     */
    private ?array $roles = [];
    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     */
    private ?string $firstName;
    /**
     * @ORM\Column(name="last_name", type="string", nullable=true)
     */
    private ?string $lastName;

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

    public function setRegistrationDate(DateTimeInterface $registrationDate): void
    {
        $this->registrationDate = $registrationDate;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function addRole(string $role): void
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
    }

    public function addRoles(array $roles): void
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }
}
