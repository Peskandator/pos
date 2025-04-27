<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Company\Enums\CompanyUserRoles;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Presenters\BaseCompanyPresenter;
use App\Product\Action\DeleteCategoryAction;
use App\Product\Forms\AddCategoryFormFactory;
use App\Product\Forms\EditCategoryFormFactory;
use App\Product\ORM\CategoryRepository;
use App\Utils\FlashMessageType;
use Nette\Application\UI\Form;

final class CategoriesPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly AddCategoryFormFactory $addCategoryFormFactory,
        private readonly EditCategoryFormFactory $editCategoryFormFactory,
        private readonly CategoryRepository $categoryRepository,
        private readonly DeleteCategoryAction $deleteCategoryAction,
    )
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $permittedRoles = $this->checkPermissionsForUser(CompanyUserRoles::getAllRoles());

        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Číselníky',
                null)
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Kategorie',
                null)
        );

        $this->template->categories = $this->currentCompany->getCategories();
        $this->template->isEditor = in_array(CompanyUserRoles::EDTIOR, $permittedRoles, true);
    }

    protected function createComponentAddCategoryForm(): Form
    {
        return $this->addCategoryFormFactory->create($this->currentCompany);
    }

    protected function createComponentEditCategoryForm(): Form
    {
        return $this->editCategoryFormFactory->create($this->currentCompany);
    }

    protected function createComponentDeleteCategoryForm(): Form
    {
        $form = new Form;

        $form
            ->addHidden('id')
            ->setRequired(true)
        ;
        $form->addSubmit('send');

        $form->onValidate[] = function (Form $form, \stdClass $values) {
            $this->checkPermissionsForUser([CompanyUserRoles::EDTIOR]);

            $category = $this->categoryRepository->find((int)$values->id);

            if (!$category) {
                $form->addError('Kategorie nebyla nalezena.');
                $this->flashMessage('Kategorie nebyla nalezena.', FlashMessageType::ERROR);
                return;
            }
            $entity = $category->getCompany();
            $form = $this->checkAccessToElementsCompany($form, $entity);
        };

        $form->onSuccess[] = function (Form $form, \stdClass $values) {
            $category = $this->categoryRepository->find((int)$values->id);
            $this->deleteCategoryAction->__invoke($category);
            $this->flashMessage('Kategorie byla smazána.', FlashMessageType::SUCCESS);
            $this->redirect('this');
        };

        return $form;
    }
}