<?php

namespace App\Product\Forms;

use App\Company\Enums\CompanyUserRoles;
use App\Entity\Company;
use App\Product\Action\AddDiningTableAction;
use App\Product\Services\CodeValidator;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

class AddDiningTableFormFactory
{
    public function __construct(
        private readonly CodeValidator        $codeValidator,
        private readonly AddDiningTableAction $addDiningTableAction,
    )
    {
    }

    public function create(Company $company): Form
    {
        $form = new Form;

        $form
            ->addInteger('number', 'Číslo')
            ->setRequired()
        ;
        $form
            ->addText('description', 'Popis')
            ->setRequired()
        ;
        $form->addSubmit('send', 'Přidat');

        $form->onValidate[] = function (Form $form, \stdClass $values) use ($company) {
            $form->getPresenter()->checkPermissionsForUser([CompanyUserRoles::EDTIOR]);

            $validationMsg = $this->codeValidator->isDiningTableNumberValid($company, $values->number);
            if ($validationMsg !== '') {
                $form['number']->addError($validationMsg);
                $form->getPresenter()->flashMessage($validationMsg,FlashMessageType::ERROR);
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($company) {
            $this->addDiningTableAction->__invoke($company, $values->number, $values->description);
            $form->getPresenter()->flashMessage('Stůl byl přidán.', FlashMessageType::SUCCESS);
            $form->getPresenter()->redirect('this');
        };

        return $form;
    }
}