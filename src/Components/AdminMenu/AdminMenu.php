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
        $signOutLink = $this->getPresenter()->lazyLink(':Home:signOut');
        $this->template->profileLink = $profileLink;
        $this->template->isProfileLinkActive = $this->getPresenter()->isLinkCurrent($profileLink->getDestination(), $profileLink->getParameters());
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

        $boardsItems = $this->buildBoardsItems();
        $menuItems[] = $this->createMenuSection(
            'Sledované desky',
            $boardsItems
        );

        return $menuItems;
    }

    private function buildBoardsItems(): array
    {
        $items[] = $this->createMenuItem(
            'Přehled',
            $this->getPresenter()->lazyLink(':Admin:Boards:default'),
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
