<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Entity\User;
use App\Presenters\BaseAdminPresenter;

final class DashboardPresenter extends BaseAdminPresenter
{
    public function __construct(
    )
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $user = $this->getCurrentUser();

        if (!isset($this->currentCompanyId)) {
            $firstCompanyId = $this->provideFirstCompanyId($user);
            if ($firstCompanyId) {
                $this->redirect(':Admin:Dashboard:default', ['currentCompanyId' => $firstCompanyId]);
            }
        }
    }

    private function provideFirstCompanyId(User $user): ?int
    {
        $companyUsers = $user->getCompanyUsers();
        if ($companyUsers->isEmpty()) {
            return null;
        }
        $companyId = $companyUsers->first()->getCompany()->getId();
        return $companyId;
    }
}