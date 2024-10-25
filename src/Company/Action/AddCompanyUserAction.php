<?php

namespace App\Company\Action;

use App\DancingClub\Enums\CompanyUserRoles;
use App\Entity\Company;
use App\Entity\CompanyUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AddCompanyUserAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Company $company, User $user, array $roles): void
    {
        $companyUser = new CompanyUser($user, $company, [CompanyUserRoles::MEMBER]);
        $companyUser->setRoles($roles);
        $this->entityManager->persist($companyUser);

        $user->getCompanyUsers()->add($companyUser);
        $company->getCompanyUsers()->add($companyUser);

        $this->entityManager->flush();
    }
}
