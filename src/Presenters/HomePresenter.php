<?php

declare(strict_types=1);

namespace App\Presenters;
use App\User\ORM\UserRepository;
use App\Utils\FlashMessageType;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\User;

final class HomePresenter extends BasePresenter
{
    public function __construct(
        private readonly User $userManager,
        private readonly Authenticator $authenticator,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect(':Admin:Dashboard:default');
        }
        $this->getSession()->start();
    }

    public function actionSignOut(): void
    {
        if ($this->session->isStarted()) {
            $this->session->destroy();
        }
        $this->getUser()->logout(true);
        $this->flashMessage('Byl jste odhlášen.');
        $this->redirect(':Home:default');
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form;
        $form
            ->addEmail('email', 'E-mailová adresa')
            ->setRequired()
        ;
        $form
            ->addPassword('password', 'Heslo')
            ->setRequired()
        ;
        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = function (Form $form, \stdClass $values) {
            try {
                $identity = $this->authenticator->authenticate($values->email, $values->password);
                $this->userManager->login($identity);
            } catch (AuthenticationException $e) {
                $errorType = $e->getMessage();

                if ($errorType === 'incorrect_password') {
                    $form->addError('Zadali jste nesprávné heslo.');
                    return;
                }
                $form->addError('Účet se zadanou e-mailovou adresou neexistuje.');
                return;
            }

            $user = $this->userRepository->findByEmail($values->email);
            if ($user instanceof \App\Entity\User) {
                $user->setLastLogonDate();
                $this->entityManager->flush();
            }

            $this->flashMessage('Přihlášení proběhlo úspěšně.', FlashMessageType::SUCCESS);
            $this->redirect('this');
        };

        return $form;
    }
}