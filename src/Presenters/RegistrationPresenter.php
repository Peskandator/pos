<?php

declare(strict_types=1);

namespace App\Presenters;

use App\User\Action\RegisterAction;
use App\User\Action\RegisterRequest;
use App\User\Exception\EmailIsAlreadyUsedException;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;


final class RegistrationPresenter extends BasePresenter
{
    private RegisterAction $registerAction;

    public function __construct(
        RegisterAction $registerAction,
    )
    {
        $this->registerAction = $registerAction;
        parent::__construct();
    }

    public function actionDefault(): void
    {
    }

    protected function createComponentRegistrationForm(): Form
    {
        $form = new Form;
        $form
            ->addText('name', 'Jméno')
            ->setRequired()
        ;
        $form
            ->addText('surname', 'Příjmení')
            ->setRequired()
        ;
        $form
            ->addEmail('email', 'E-mailová adresa')
            ->setRequired()
        ;

        $form
            ->addPassword('password', 'Heslo')
            ->addRule($form::MIN_LENGTH, 'Heslo musí obsahovat minimálně 7 znaků.', 7)
            ->setRequired()
        ;

        $form->addSubmit('send', 'Registrovat');
        $form->onSuccess[] = function (Form $form, \stdClass $values) {
            $request = new RegisterRequest(
                $values->name,
                $values->surname,
                $values->email,
                $values->password,
                '',
            );

            try {
                $this->registerAction->__invoke($request);
            } catch (EmailIsAlreadyUsedException $e) {
                $form->getComponent('email')->addError('Účet se zadanou e-mailovou adresou již existuje');
                $this->flashMessage('Účet se zadanou e-mailovou adresou již existuje', FlashMessageType::ERROR);
                return;
            }
            $this->flashMessage('Byl jste úspěšně registrován.');
            $this->redirect(':Home:default');
        };

        return $form;
    }
}