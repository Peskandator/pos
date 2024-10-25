<?php

namespace App\Company\Action;

use App\Entity\CompanyUser;
use Doctrine\ORM\EntityManagerInterface;

class DeleteCompanyUserAction
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CompanyUser $companyUser): void
    {
        $this->entityManager->remove($companyUser);
        $this->entityManager->flush();
    }
}
