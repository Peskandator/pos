<?php

namespace App\Company\Action;

use App\Company\Requests\CreateCompanyRequest;
use App\DancingClub\Enums\CompanyUserRoles;
use App\Entity\Company;
use App\Entity\CompanyUser;
use App\Utils\CurrentUser;
use Doctrine\ORM\EntityManagerInterface;

class CreateCompanyAction
{
    private EntityManagerInterface $entityManager;
    private CurrentUser $currentUser;

    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentUser $currentUser
    ) {
        $this->entityManager = $entityManager;
        $this->currentUser = $currentUser;
    }

    public function __invoke(CreateCompanyRequest $request): int
    {
        $user = $this->currentUser->getCurrentLoggedInUser();

        $entity = new Company($request);
        $this->entityManager->persist($entity);

        $roles = [CompanyUserRoles::ADMIN];

        $entityUser = new CompanyUser($user, $entity, $roles);
        $this->entityManager->persist($entityUser);

        $user->getCompanyUsers()->add($entityUser);
        $entity->getCompanyUsers()->add($entityUser);

        $this->entityManager->flush();

        return $entity->getId();
    }
}
