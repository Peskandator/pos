<?php

namespace App\Presenters;

use App\Components\AdminMenu\AdminMenu;
use App\Components\AdminMenu\AdminMenuFactoryInterface;
use App\Components\Breadcrumb\Breadcrumb;
use App\Components\Breadcrumb\BreadcrumbFactoryInterface;
use App\Entity\User;
use App\Utils\CurrentUser;
use App\Utils\FlashMessageType;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

abstract class BaseAdminPresenter extends Presenter
{
    private CurrentUser $currentUser;
    private AdminMenuFactoryInterface $adminMenuFactory;
    private BreadcrumbFactoryInterface $breadcrumbFactory;

    public function injectBaseDeps(
    ) {
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

    public function getCurrentUser(): User
    {
        $loggedInUser = $this->currentUser->getCurrentLoggedInUser();
        if ($loggedInUser === null) {
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
