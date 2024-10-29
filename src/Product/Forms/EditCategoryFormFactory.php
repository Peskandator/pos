<?php

namespace App\Product\Forms;

use App\Entity\Company;
use App\Product\Action\EditCategoryAction;
use App\Product\ORM\CategoryRepository;
use App\Product\Services\CodeValidator;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

class EditCategoryFormFactory
{
    public function __construct(
        private readonly CodeValidator $codeValidator,
        private readonly CategoryRepository $categoryRepository,
        private readonly EditCategoryAction $editCategoryAction,
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
            ->addInteger('code', 'Kód')
            ->setRequired()
        ;
        $form
            ->addText('name', 'Název')
            ->setRequired()
        ;
        $form->addSubmit('send', 'Upravit');

        $form->onValidate[] = function (Form $form, \stdClass $values) use ($company) {
            $category = $this->categoryRepository->find($values->id);
            if ($category === null) {
                $errMsg = 'Kategorie nebyla nalezena.';
                $form->addError($errMsg);
                $form->getPresenter()->flashMessage($errMsg,FlashMessageType::ERROR);
            }
            if ($category->getCompany()->getId() !== $company->getId()) {
                $errMsg = 'K této akci nemáte přístup.';
                $form->addError($errMsg);
                $form->getPresenter()->flashMessage($errMsg,FlashMessageType::ERROR);
            }

            $validationMsg = $this->codeValidator->isCategoryCodeValid($company, $values->code, $category->getCode());
            if ($validationMsg !== '') {
                $form->addError($validationMsg);
                $form->getPresenter()->flashMessage($validationMsg,FlashMessageType::ERROR);
            }
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($company) {
            $category = $this->categoryRepository->find($values->id);
            $this->editCategoryAction->__invoke($category, $values->code, $values->name);
            $form->getPresenter()->flashMessage('Kategorie byla upravena.', FlashMessageType::SUCCESS);
            $form->getPresenter()->redirect('this');
        };

        return $form;
    }
}