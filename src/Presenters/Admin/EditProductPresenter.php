<?php

declare(strict_types=1);

namespace App\Presenters\Admin;
use App\Components\Breadcrumb\BreadcrumbItem;
use App\Presenters\BaseCompanyPresenter;
use App\Product\Forms\ProductFormFactory;
use Nette\Application\UI\Form;

final class EditProductPresenter extends BaseCompanyPresenter
{

    public function __construct(
        private readonly ProductFormFactory $productFormFactory,
    )
    {
        parent::__construct();
    }

    public function actionDefault(int $productId): void
    {
        $product = $this->findProductById($productId);

        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Číselníky',
                null)
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                'Produkty',
                $this->lazyLink(':Admin:Products:default'))
        );
        $this->getComponent('breadcrumb')->addItem(
            new BreadcrumbItem(
                $product->getName(),
                null)
        );

        $this->template->product = $product;
        $this->template->singleProductsOptions = $this->currentCompany->getSingleProducts();
    }

    protected function createComponentEditProductForm(): Form
    {
        $product = $this->template->product;
        $form = $this->productFormFactory->create($this->currentCompany, $product);

        $this->productFormFactory->fillInForm($form, $product);
        return $form;
    }
}