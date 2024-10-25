<?php

namespace App\Components\AdminMenu;

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

    public function render()
    {
        $sections = $this->buildMenuItems();


        $this->template->sections = $sections;

        $profileLink = $this->getPresenter()->lazyLink(':Admin:Profile:default');
        $companiesLink = $this->getPresenter()->lazyLink(':Admin:Companies:default');
        $signOutLink = $this->getPresenter()->lazyLink(':Home:signOut');
        $this->template->profileLink = $profileLink;
        $this->template->isProfileLinkActive = $this->getPresenter()->isLinkCurrent(
            $profileLink->getDestination(),
            $profileLink->getParameters()
        );

        $isCompaniesLinkActive = $this->getPresenter()->isLinkCurrent(
                $companiesLink->getDestination(),
                $companiesLink->getParameters()
            )
            || $this->getPresenter()->getName() === 'Admin:Companies'
        ;

        $this->template->companiesLink = [
            $companiesLink,
            $isCompaniesLinkActive
        ];
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

    private function buildMenuItems(): array
    {
        $menuItems = [];

        $loggedInUser = $this->currentUserManager->getCurrentLoggedInUser();
        if ($loggedInUser === null) {
            throw new NoSignedInUserException();
        }

        $ordersItems = $this->buildOrdersItems();
        $menuItems[] = $this->createMenuSection(
            'Objednávky',
            $ordersItems
        );

        return $menuItems;
    }

    private function buildOrdersItems(): array
    {
        $items[] = $this->createMenuItem(
            'Přehled',
            $this->getPresenter()->lazyLink(':Admin:Companies:default'),
            [],
            $this->getCurrentLinkCallable()
        );

        return $items;
    }

    private function getCurrentLinkCallable(): callable
    {
        return function (Link $link) {
            return $this->getPresenter()->isLinkCurrent($link->getDestination(), $link->getParameters());
        };
    }
}
