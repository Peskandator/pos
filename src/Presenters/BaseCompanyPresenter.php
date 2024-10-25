<?php

namespace App\Presenters;

abstract class BaseCompanyPresenter extends BaseAdminPresenter
{
    public function checkRequirements($element): void
    {
        parent::checkRequirements($element);
        if (isset($this->currentCompanyId)) {
            $currentCompany = $this->findCompanyById();
            if ($currentCompany === null) {
                $this->redirect(':Admin:Dashboard:default', ['$currentCompanyId' => null]);
            }
        } else {
            $this->redirect(':Admin:Dashboard:default', ['$currentCompanyId' => null]);
        }
    }
}