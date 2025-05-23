<?php

namespace App\Components\AdminMenu;

use App\Entity\Company;
use App\User\Exception\NoSignedInUserException;
use App\Utils\CurrentUser;
use Nette\Application\UI\Control;
use Nette\Application\UI\Link;

class AdminMenu extends Control
{
    private CurrentUser $currentUserManager;

    public function __construct(CurrentUser $currentUser)
    {
        $this->currentUserManager = $currentUser;
    }

    public function render(?Company $company, string $mode = 'desktop'): void
    {
        $sections = $this->buildMenuItems($company);
        $this->template->currentCompany = $company;
        $this->template->sections = $sections;
        $this->template->mode = $mode;

        $profileLink = $this->getPresenter()->lazyLink(':Admin:Profile:default');
        $companiesLink = $this->getPresenter()->lazyLink(':Admin:Companies:default');
        $signOutLink = $this->getPresenter()->lazyLink(':Home:signOut');
        $this->template->profileLink = $profileLink;
        $this->template->isProfileLinkActive = $this->getPresenter()->isLinkCurrent(
            $profileLink->getDestination(),
            $profileLink->getParameters()
        );

        $isCompaniesLinkActive = $this->getPresenter()->isLinkCurrent($companiesLink->getDestination(), $companiesLink->getParameters())
            || $this->getPresenter()->getName() === 'Admin:Companies';

        $this->template->companiesLink = [$companiesLink, $isCompaniesLinkActive];
        $this->template->signOutLink = $signOutLink;
        $this->template->setFile(__DIR__ . '/templates/menu.latte');
        $this->template->render();
    }

    private function createMenuSection(string $text, array $items): MenuSection
    {
        return new MenuSection($text, $items);
    }

    private function createMenuItem(string $text, ?Link $link, array $children, ?callable $isLinkCurrent): MenuItem
    {
        return new MenuItem(
            $text,
            $link,
            $children,
            $isLinkCurrent,
        );
    }

    private function buildMenuItems(?Company $company): array
    {
        $menuItems = [];

        $loggedInUser = $this->currentUserManager->getCurrentLoggedInUser();
        if ($loggedInUser === null) {
            throw new NoSignedInUserException();
        }

        if ($company === null) {
            return $menuItems;
        }

        $companyUser = $loggedInUser->getCompanyUser($company);
        if ($companyUser === null) {
            return $menuItems;
        }

        $ordersItems = $this->buildOrdersItems();
        $menuItems[] = $this->createMenuSection(
            'Objednávky',
            $ordersItems
        );

        $productManagementItems = $this->buildProductManagementItems();
        $menuItems[] = $this->createMenuSection(
            'Číselníky',
            $productManagementItems
        );

        if ($companyUser->isAdmin()) {
            $statisticItems = $this->buildStatisticsItems();
            $menuItems[] = $this->createMenuSection(
                'Statistika',
                $statisticItems
            );
        }

        return $menuItems;
    }

    private function buildOrdersItems(): array
    {
        $items[] = $this->createMenuItem(
            'Přehled',
            $this->getPresenter()->lazyLink(':Admin:Orders:default'),
            [],
            $this->getCurrentLinkCallable()
        );

        $items[] = $this->createMenuItem(
            'Přidat objednávku',
            $this->getPresenter()->lazyLink(':Admin:Orders:createNew'),
            [],
            $this->getCurrentLinkCallable()
        );

        return $items;
    }

    private function buildProductManagementItems(): array
    {
        $items[] = $this->createMenuItem(
            'Produkty',
            $this->getPresenter()->lazyLink(':Admin:Products:default'),
            [],
            $this->getCurrentLinkCallable(['Admin:EditProduct'])
        );
        $items[] = $this->createMenuItem(
            'Kategorie',
            $this->getPresenter()->lazyLink(':Admin:Categories:default'),
            [],
            $this->getCurrentLinkCallable()
        );
        $items[] = $this->createMenuItem(
            'Stoly',
            $this->getPresenter()->lazyLink(':Admin:DiningTables:default'),
            [],
            $this->getCurrentLinkCallable()
        );

        return $items;
    }

    private function buildStatisticsItems(): array
    {
        $items[] = $this->createMenuItem(
            'Statistika',
            $this->getPresenter()->lazyLink(':Admin:Statistics:default'),
            [],
            $this->getCurrentLinkCallable()
        );

        return $items;
    }

    private function getCurrentLinkCallable(array $additionalPresenters = []): callable
    {
        return function (Link $link) use ($additionalPresenters) {
            if (in_array($this->getPresenter()->getName(), $additionalPresenters, true)) {
                return true;
            }

            return $this->getPresenter()->isLinkCurrent($link->getDestination(), $link->getParameters());
        };
    }
}
