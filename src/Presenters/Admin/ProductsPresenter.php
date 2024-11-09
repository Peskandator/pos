<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Presenters\BaseCompanyPresenter;
use App\Product\Forms\ProductFormFactory;
use Nette\Application\UI\Form;

final class ProductsPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly ProductFormFactory $addProductFormFactory,
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
                'Produkty',
                null)
        );

        $this->template->products = $this->currentCompany->getProducts();
        $this->template->productsInGroupOptions = $this->currentCompany->getSingleProducts();
    }

    protected function createComponentAddProductForm(): Form
    {
        return $this->addProductFormFactory->create($this->currentCompany, null);
    }
}