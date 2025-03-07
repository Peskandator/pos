<?php

namespace App\Product\Forms;

use App\Entity\Company;
use App\Product\Action\EditDiningTableAction;
use App\Product\ORM\DiningTableRepository;
use App\Product\Services\CodeValidator;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

class EditDiningTableFormFactory
{
    public function __construct(
        private readonly CodeValidator         $codeValidator,
        private readonly DiningTableRepository $tableRepository,
        private readonly EditDiningTableAction $editTableAction,
    )
    {
    }

    public function create(Company $company): Form
    {
        $form = new Form;

        $form
            ->addHidden('id')
            ->setRequired()
        ;
        $form
            ->addInteger('number', 'Číslo stolu')
            ->setRequired()
        ;
        $form
            ->addText('description', 'Popis')
            ->setRequired()
        ;
        $form->addSubmit('send', 'Upravit');

        $form->onValidate[] = function (Form $form, \stdClass $values) use ($company) {
            $table = $this->tableRepository->find($values->id);
            if ($table === null) {
                $errMsg = 'Stůl nebyl nalezen.';
                $form->addError($errMsg);
                $form->getPresenter()->flashMessage($errMsg,FlashMessageType::ERROR);
            }
            if ($table->getCompany()->getId() !== $company->getId()) {
                $errMsg = 'K této akci nemáte přístup.';
                $form->addError($errMsg);
                $form->getPresenter()->flashMessage($errMsg,FlashMessageType::ERROR);
            }

            $validationMsg = $this->codeValidator->isDiningTableNumberValid($company, $values->number, $table->getNumber());
            if ($validationMsg !== '') {
                $form->addError($validationMsg);
                $form->getPresenter()->flashMessage($validationMsg,FlashMessageType::ERROR);
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($company) {
            $table = $this->tableRepository->find($values->id);
            $this->editTableAction->__invoke($table, $values->number, $values->description);
            $form->getPresenter()->flashMessage('Stůl byl upraven.', FlashMessageType::SUCCESS);
            $form->getPresenter()->redirect('this');
        };

        return $form;
    }
}