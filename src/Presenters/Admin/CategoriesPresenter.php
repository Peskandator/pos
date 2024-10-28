<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Presenters\BaseCompanyPresenter;
use App\Product\Forms\AddCategoryFormFactory;
use Nette\Application\UI\Form;

final class CategoriesPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly AddCategoryFormFactory $addCategoryFormFactory,
    )
    {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Správa produktů',
                null)
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Kategorie',
                null)
        );

        $this->template->categories = $this->currentCompany->getCategories();
    }

    protected function createComponentAddCategoryForm(): Form
    {
        return $this->addCategoryFormFactory->create($this->currentCompany);
    }

}