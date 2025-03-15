<?php

namespace App\Product\Forms;

use App\Entity\Company;
use App\Product\Action\AddCategoryAction;
use App\Product\Services\CodeValidator;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

class AddCategoryFormFactory
{
    public function __construct(
        private readonly CodeValidator $codeValidator,
        private readonly AddCategoryAction $addCategoryAction,
    )
    {
    }

    public function create(Company $company): Form
    {
        $form = new Form;

        $form
            ->addInteger('code', 'Kód')
            ->setRequired()
        ;
        $form
            ->addText('name', 'Název')
            ->setMaxLength(50)
            ->setRequired()
        ;
        $form->addSubmit('send', 'Přidat');

        $form->onValidate[] = function (Form $form, \stdClass $values) use ($company) {
            $validationMsg = $this->codeValidator->isCategoryCodeValid($company, $values->code);
            if ($validationMsg !== '') {
                $form['code']->addError($validationMsg);
                $form->getPresenter()->flashMessage($validationMsg,FlashMessageType::ERROR);
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($company) {
            $this->addCategoryAction->__invoke($company, $values->code, $values->name);
            $form->getPresenter()->flashMessage('Kategorie byla přidána.', FlashMessageType::SUCCESS);
            $form->getPresenter()->redirect('this');
        };

        return $form;
    }
}