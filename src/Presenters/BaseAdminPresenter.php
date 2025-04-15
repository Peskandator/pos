<?php

namespace App\Presenters;

use App\Company\ORM\CompanyRepository;
use App\Components\AdminMenu\AdminMenu;
use App\Components\AdminMenu\AdminMenuFactoryInterface;
use App\Components\Breadcrumb\Breadcrumb;
use App\Components\Breadcrumb\BreadcrumbFactoryInterface;
use App\Entity\Company;
use App\Entity\User;
use App\Order\ORM\OrderRepository;
use App\Product\ORM\ProductRepository;
use App\Utils\CurrentUser;
use App\Utils\FlashMessageType;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

abstract class BaseAdminPresenter extends Presenter
{
    #[Persistent]
    public int $currentCompanyId;
    protected CompanyRepository $companyRepository;
    protected ProductRepository $productRepository;
    protected OrderRepository $orderRepository;
    private CurrentUser $currentUser;
    private AdminMenuFactoryInterface $adminMenuFactory;
    private BreadcrumbFactoryInterface $breadcrumbFactory;

    public Company $currentCompany;

    public function injectBaseDeps(
        CompanyRepository $companyRepository,
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
    ) {
        $this->companyRepository = $companyRepository;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
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


        if (isset($this->currentCompanyId) && $this->currentCompanyId) {
            $currentCompany = $this->companyRepository->find($this->currentCompanyId);

            if ($currentCompany === null) {
                $this->redirect(':Admin:Dashboard:default', ['currentCompanyId' => null]);
            }
            $currentUser = $this->getCurrentUser();
            if (!$currentUser->isCompanyUser($currentCompany)) {
                $this->addNoPermissionError(true);
            }

            $this->currentCompany = $currentCompany;
        }
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

    public function addNoPermissionError(bool $unsetCompany = false): void
    {
        $this->flashMessage(
            'K této akci nemáte oprávnění',
            FlashMessageType::ERROR
        );

        if ($unsetCompany) {
            $this->redirect(':Admin:Dashboard:default', ['currentCompanyId' => null]);
        }

        $this->redirect(':Admin:Dashboard:default');
    }

    public function findCompanyById(): ?Company
    {
        return $this->companyRepository->find($this->currentCompanyId);
    }

    protected function checkAccessToElementsCompany(Form $form, ?Company $company): Form
    {
        if (!$company || $company->getId() !== $this->currentCompanyId) {
            $form->addError('K této akci nemáte oprávnění.');
            $this->flashMessage('K této akci nemáte oprávnění',FlashMessageType::ERROR);
        }

        return $form;
    }
}
