<?php

namespace App\Company\Action;

use App\Company\Requests\CreateCompanyRequest;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;

class EditCompanyAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function __invoke(CreateCompanyRequest $request, Company $company): void
    {
        $company->update($request);
        $this->entityManager->flush();
    }
}
