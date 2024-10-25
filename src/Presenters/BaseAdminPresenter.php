<?php

namespace App\Presenters;

use App\Company\ORM\CompanyRepository;
use App\Components\AdminMenu\AdminMenu;
use App\Components\AdminMenu\AdminMenuFactoryInterface;
use App\Components\Breadcrumb\Breadcrumb;
use App\Components\Breadcrumb\BreadcrumbFactoryInterface;
use App\Entity\User;
use App\Utils\CurrentUser;
use App\Utils\FlashMessageType;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Presenter;

abstract class BaseAdminPresenter extends Presenter
{
    #[Persistent]
    public int $currentCompanyId;
    protected CompanyRepository $companyRepository;
    private CurrentUser $currentUser;
    private AdminMenuFactoryInterface $adminMenuFactory;
    private BreadcrumbFactoryInterface $breadcrumbFactory;

    public function injectBaseDeps(
        CompanyRepository $companyRepository,
    ) {
        $this->companyRepository = $companyRepository;
    }

    public function injectAdminMenuFactory(
        AdminMenuFactoryInterface $adminMenuFactory,
        BreadcrumbFactoryInterface $breadcrumbFactory
    )
    {
        $this->adminMenuFactory = $adminMenuFactory;
        $this->breadcrumbFactory = $breadcrumbFactory;
    }

    public function beforeRender()
    {
        $currentCompany = null;

        if (isset($this->currentCompanyId)) {
            $currentCompany = $this->companyRepository->find($this->currentCompanyId);
        }
        $this->template->currentCompany = $currentCompany;
    }

    public function injectCurrentUser(
        CurrentUser $currentUser
    )
    {
        $this->currentUser = $currentUser;
    }

    protected function createComponentAdminMenu(): AdminMenu
    {
        return $this->adminMenuFactory->create();
    }

    protected function createComponentBreadcrumb(): Breadcrumb
    {
        return $this->breadcrumbFactory->create();
    }

    public function checkRequirements($element): void
    {
        parent::checkRequirements($element);
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Home:default');
        }
        $currentUser = $this->getCurrentUser();
    }

    public function checkCompanyAdmin(): void
    {
        $currentCompany = $this->companyRepository->find($this->currentCompanyId);
        if (!$currentCompany) {
            $this->addNoPermissionError();
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isCompanyAdmin($currentCompany)) {
            $this->addNoPermissionError();
        }
    }

    public function getCurrentUser(): User
    {
        $loggedInUser = $this->currentUser->getCurrentLoggedInUser();

        if ($loggedInUser === null) {
            if ($this->session->isStarted()) {
                $this->session->destroy();
            }
            $this->getUser()->logout(true);
            $this->redirect(':Home:default');
        }
        return $loggedInUser;
    }

    public function addNoPermissionError(): void
    {
        $this->flashMessage(
            'K této akci nemáte oprávnění',
            FlashMessageType::ERROR
        );

        $this->redirect(':Admin:Dashboard:default');
    }
}
